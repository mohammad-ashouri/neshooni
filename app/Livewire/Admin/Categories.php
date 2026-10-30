<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * مدیریت دسته‌بندی درختی (حداکثر سه سطح).
 *
 * قانون جابه‌جایی: عمق والد + ۱ + تعداد سطح‌های زیر دسته نباید از MAX_DEPTH بیشتر شود،
 * و دسته نمی‌تواند زیرمجموعه خودش یا فرزندانش شود.
 * چون پیج‌ها به عمیق‌ترین دسته وصل‌اند، جابه‌جایی دسته خودبه‌خود فیلترها را درست می‌کند.
 */
#[Layout('layouts::admin'), Title('دسته‌بندی‌ها')]
class Categories extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $icon = '';

    public $parent_id = '';

    public int $sort = 0;

    public function create(?int $parentId = null): void
    {
        $this->resetForm();
        $this->parent_id = $parentId ?? '';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->resetForm();
        $this->fill([
            'editingId' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon ?? '',
            'parent_id' => $category->parent_id ?? '',
            'sort' => $category->sort,
            'showForm' => true,
        ]);
    }

    public function save(): void
    {
        $this->slug = Str::slug($this->slug ?: $this->name) ?: Str::lower(Str::random(6));
        $parentId = $this->parent_id ?: null;

        $this->validate([
            'name' => ['required', 'string', 'max:60'],
            // نامک فقط بین خواهر و برادرها یکتا باشد (مسیر کامل در آدرس استفاده می‌شود)
            'slug' => ['required', 'alpha_dash', 'max:60', Rule::unique('categories')->where('parent_id', $parentId)->ignore($this->editingId)],
            'icon' => ['nullable', 'string', 'max:8'],
            'parent_id' => ['nullable', Rule::in($this->allowedParents()->pluck('id')->all())],
            'sort' => ['integer'],
        ], ['parent_id.in' => 'این دسته نمی‌تواند زیرمجموعه دسته انتخاب‌شده باشد (حداکثر سه سطح).']);

        Category::updateOrCreate(['id' => $this->editingId], [
            'name' => trim($this->name),
            'slug' => $this->slug,
            'icon' => $this->icon ?: null,
            'parent_id' => $parentId,
            'sort' => $this->sort,
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'دسته ذخیره شد.');
    }

    public function delete(int $id): void
    {
        $category = Category::findOrFail($id);

        if ($category->subs()->isNotEmpty() || Listing::where('category_id', $id)->exists()) {
            $this->dispatch('toast', message: 'این دسته زیردسته یا پیج دارد؛ اول آن‌ها را منتقل کنید.', type: 'error');

            return;
        }

        $category->delete();
        $this->dispatch('toast', message: 'دسته حذف شد.');
    }

    /** دسته‌هایی که می‌توانند والد دسته در حال ویرایش باشند */
    private function allowedParents()
    {
        $editing = Category::fromCache($this->editingId);
        $height = $editing?->height() ?? 0;
        $blocked = $editing?->selfAndDescendantIds() ?? [];

        return Category::flatTree()
            ->filter(fn ($row) => $row['depth'] + 1 + $height <= Category::MAX_DEPTH && ! in_array($row['category']->id, $blocked))
            ->pluck('category');
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'slug', 'icon', 'parent_id', 'sort');
        $this->resetErrorBag();
    }

    public function render()
    {
        $counts = Listing::selectRaw('category_id, count(*) as total')->groupBy('category_id')->pluck('total', 'category_id');

        return view('livewire.admin.categories', [
            'tree' => Category::flatTree(),
            'counts' => $counts,
            'parents' => $this->showForm ? $this->allowedParents() : collect(),
        ]);
    }
}
