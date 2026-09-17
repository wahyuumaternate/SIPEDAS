<?php

namespace Database\Factories;

use App\Models\Role;
use App\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Default: seluruh permission, supaya test yang tidak fokus menguji
     * otorisasi tidak perlu mengurus hak_akses satu per satu. Test yang
     * memang menguji pembatasan akses sebaiknya memakai state role tertentu
     * atau menimpa 'hak_akses' secara eksplisit.
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->jobTitle(),
            'slug' => fake()->unique()->slug(2),
            'deskripsi' => fake()->sentence(),
            'hak_akses' => Permission::all(),
            'is_active' => true,
        ];
    }
}
