<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Bersihkan input sebelum divalidasi.
     *
     * Ini bukan pengganti parameter binding (yang sudah otomatis dipakai
     * Eloquent/Query Builder di Auth::attempt), tapi lapisan tambahan
     * supaya karakter berbahaya (tag HTML, null byte, whitespace berlebih)
     * ditolak sedini mungkin, sebelum masuk ke validasi atau rate limiter key.
     */
    protected function prepareForValidation(): void
    {
        $email = (string) $this->input('email');

        // Buang null byte & karakter kontrol non-printable (defense-in-depth,
        // bukan spesifik SQLi/XSS tapi mencegah trik null-byte/control-char injection).
        $email = preg_replace('/[\x00-\x1F\x7F]/u', '', $email) ?? '';

        // Normalisasi: trim spasi & lowercase, supaya "Admin@x.com " dan "admin@x.com"
        // dianggap sama (juga menghindari bypass rate-limit lewat variasi kapitalisasi/spasi).
        $email = Str::of($email)->trim()->lower()->value();

        $this->merge(['email' => $email]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            // 'email:rfc,filter' mewajibkan format valid menurut RFC *dan* PHP filter_var.
            // Kombinasi ini menolak karakter seperti < > " ' ; ( ) yang tidak sah dalam
            // alamat email biasa — jadi payload XSS/SQLi semacam
            // "<script>alert(1)</script>" atau "' OR '1'='1" otomatis gagal validasi
            // sebelum sempat diproses lebih lanjut.
            'email' => ['required', 'string', 'max:255', 'email:rfc,filter'],

            // Password tidak perlu dibatasi karakternya (simbol harus tetap boleh),
            // cukup dibatasi panjangnya untuk mencegah payload raksasa (DoS) —
            // bcrypt sendiri hanya memproses 72 byte pertama.
            'password' => ['required', 'string', 'max:255'],

            'h-captcha-response' => ['required', 'captcha'],
            // Honeypot: field ini harus SELALU kosong. Bot biasanya mengisi semua field.
            'website' => ['prohibited'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotBot();
        $this->ensureIsNotRateLimited();

        // $this->only('email', 'password') di sini aman dari SQL injection karena
        // Auth::attempt() meneruskannya ke Query Builder, yang selalu memakai
        // prepared statement (parameter binding) — bukan string SQL mentah.
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit($this->ipThrottleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->ipThrottleKey());
    }

    /**
     * Deteksi bot lewat honeypot dan kecepatan submit form.
     * Form dianggap mencurigakan kalau diisi kurang dari 2 detik
     * sejak halaman dimuat — manusia butuh waktu lebih lama dari itu.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotBot(): void
    {
        if (filled($this->input('website'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $renderedAt = $this->input('form_rendered_at');

        if ($renderedAt && (time() - (int) $renderedAt) < 2) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Batas per akun + IP (brute force akun tertentu)
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $this->throwLockoutException($this->throttleKey());
        }

        // Batas per IP saja (banyak akun dicoba dari satu sumber / distribusi bot)
        if (RateLimiter::tooManyAttempts($this->ipThrottleKey(), 20)) {
            $this->throwLockoutException($this->ipThrottleKey());
        }
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwLockoutException(string $key): void
    {
        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request (per akun + IP).
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * Get the rate limiting throttle key based on IP only.
     */
    public function ipThrottleKey(): string
    {
        return 'login-ip|'.$this->ip();
    }
}