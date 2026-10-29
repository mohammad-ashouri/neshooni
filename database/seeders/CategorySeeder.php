<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * درخت دسته‌بندی‌ها: [نام، نامک، آیکون، [زیردسته‌ها]]
     * هر زیردسته: [نام، نامک، [زیرِزیردسته‌ها به شکل نامک => نام]]
     */
    public function run(): void
    {
        $tree = [
            ['پوشاک و مد', 'fashion', '👗', [
                ['زنانه', 'women', ['manteau' => 'مانتو', 'blouse' => 'شومیز و تاپ', 'women-pants' => 'شلوار', 'evening-dress' => 'لباس مجلسی']],
                ['مردانه', 'men', ['shirt' => 'پیراهن', 'men-pants' => 'شلوار', 'suit' => 'کت و شلوار']],
                ['بچگانه', 'kids', []],
                ['کیف و کفش', 'bags-shoes', []],
                ['اکسسوری', 'accessories', []],
            ]],
            ['آرایشی و بهداشتی', 'beauty', '💄', [
                ['لوازم آرایشی', 'makeup', []],
                ['مراقبت پوست و مو', 'skincare', []],
                ['عطر و ادکلن', 'perfume', []],
            ]],
            ['غذا و رستوران', 'food', '🍕', [
                ['کافه و شیرینی', 'cafe', ['coffee-shop' => 'کافه', 'confectionery' => 'قنادی', 'homemade-cake' => 'کیک خانگی']],
                ['رستوران و فست‌فود', 'restaurant', []],
                ['غذای خانگی', 'homemade', []],
            ]],
            ['آموزش', 'education', '📚', [
                ['زبان خارجی', 'languages', ['english' => 'انگلیسی', 'german' => 'آلمانی', 'turkish' => 'ترکی']],
                ['کنکور و درسی', 'school', []],
                ['مهارت و هنر', 'skills', []],
            ]],
            ['کالای دیجیتال', 'digital', '📱', [
                ['موبایل و تبلت', 'mobile', ['phones' => 'گوشی موبایل', 'tablets' => 'تبلت']],
                ['لپ‌تاپ و کامپیوتر', 'computer', []],
                ['لوازم جانبی', 'gadget-accessories', ['headphones' => 'هدفون و هندزفری', 'chargers' => 'شارژر و کابل', 'cases' => 'قاب و گلس']],
                ['ساعت و گجت هوشمند', 'smart-gadgets', []],
            ]],
            ['خانه و دکوراسیون', 'home', '🛋️', [
                ['دکوراسیون', 'decor', []],
                ['لوازم آشپزخانه', 'kitchen', []],
                ['مبلمان', 'furniture', []],
            ]],
            ['هنر و صنایع دستی', 'art', '🎨', [
                ['سفال و سرامیک', 'pottery', []],
                ['نقاشی و تابلو', 'painting', []],
                ['دوخت و بافت', 'sewing', []],
            ]],
            ['خدمات', 'services', '🛠️', [
                ['طراحی داخلی', 'interior-design', []],
                ['تعمیرات', 'repairs', []],
                ['حمل و نقل', 'transport', []],
            ]],
            ['سلامت و ورزش', 'health', '💪', [
                ['باشگاه و فیتنس', 'fitness', ['bodybuilding' => 'بدنسازی', 'yoga' => 'یوگا و پیلاتس']],
                ['تغذیه و رژیم', 'nutrition', []],
            ]],
            ['گردشگری', 'travel', '✈️', [
                ['تور داخلی', 'domestic-tours', []],
                ['تور خارجی', 'foreign-tours', ['turkey-tours' => 'ترکیه', 'uae-tours' => 'امارات']],
                ['اقامتگاه', 'stays', []],
            ]],
        ];

        foreach ($tree as $sort => [$name, $slug, $icon, $subs]) {
            $root = Category::create(compact('name', 'slug', 'icon', 'sort'));

            foreach ($subs as $subSort => [$subName, $subSlug, $leaves]) {
                $sub = Category::create(['parent_id' => $root->id, 'name' => $subName, 'slug' => $subSlug, 'sort' => $subSort]);

                $leafSort = 0;
                foreach ($leaves as $leafSlug => $leafName) {
                    Category::create(['parent_id' => $sub->id, 'name' => $leafName, 'slug' => $leafSlug, 'sort' => $leafSort++]);
                }
            }
        }
    }
}
