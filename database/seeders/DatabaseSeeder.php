<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * داده‌های پایه (نقش‌ها، پلتفرم‌ها، دسته‌ها) همیشه لازم‌اند.
     * DemoSeeder فقط برای محیط توسعه/نمایش است و در production اجرا نمی‌شود.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PlatformSeeder::class,
            CategorySeeder::class,
        ]);

        if (! app()->isProduction()) {
            $this->call(DemoSeeder::class);
        }
    }
}
