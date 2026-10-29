<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

/**
 * راهنمای استفاده؛ متن راهنما در پوشه docs به صورت Markdown نوشته شده
 * تا هم در گیت‌هاب خوانا باشد و هم همین‌جا در سایت نمایش داده شود.
 */
class GuideController extends Controller
{
    public function site()
    {
        return $this->show('guide-site.md', 'راهنمای استفاده از نشونی', 'app');
    }

    public function panel()
    {
        return $this->show('guide-panel.md', 'راهنمای پنل مدیریت', 'admin');
    }

    private function show(string $file, string $title, string $layout)
    {
        $path = base_path('docs/'.$file);
        abort_unless(is_file($path), 404);

        return view('pages.guide', [
            'title' => $title,
            'layout' => $layout,
            'html' => Str::markdown(file_get_contents($path), ['html_input' => 'strip']),
        ]);
    }
}
