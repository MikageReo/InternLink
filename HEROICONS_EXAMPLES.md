# 🎨 Heroicons Usage Examples

## ✅ What I've Set Up For You

1. **Created a reusable component**: `resources/views/components/heroicon.blade.php`
2. **Updated navigation examples**: Replaced some SVG icons with Heroicons
3. **Created this guide**: To help you use Heroicons throughout your app

## 📝 How to Use

### Basic Usage

```blade
{{-- Simple icon --}}
<x-heroicon name="home" />

{{-- With custom size --}}
<x-heroicon name="user" class="w-6 h-6" />

{{-- With color --}}
<x-heroicon name="academic-cap" class="w-5 h-5 text-blue-500" />
```

### In Navigation Links

```blade
{{-- Before --}}
<a href="/dashboard" class="flex items-center">
    <i class="fas fa-home mr-2"></i>
    Dashboard
</a>

{{-- After --}}
<a href="/dashboard" class="flex items-center">
    <x-heroicon name="home" class="w-4 h-4 mr-2" />
    Dashboard
</a>
```

### In Buttons

```blade
<button class="flex items-center px-4 py-2 bg-blue-600 text-white rounded">
    <x-heroicon name="user" class="w-5 h-5 mr-2" />
    Add User
</button>
```

### In Cards/Statistics

```blade
<div class="flex items-center">
    <div class="bg-blue-100 rounded p-3">
        <x-heroicon name="users" class="w-6 h-6 text-blue-600" />
    </div>
    <div class="ml-4">
        <p class="text-sm text-gray-500">Total Students</p>
        <p class="text-2xl font-bold">150</p>
    </div>
</div>
```

### With Alpine.js (Dropdowns)

```blade
<div x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center">
        Menu
        <x-heroicon name="arrow-down" class="ml-1 w-4 h-4" />
    </button>
</div>
```

## 🎯 Common Replacements

### FontAwesome → Heroicons

| FontAwesome | Heroicon | Usage |
|------------|----------|-------|
| `fa-home` | `home` | Dashboard, Home |
| `fa-user` | `user` | Single user |
| `fa-users` | `users` | Multiple users |
| `fa-graduation-cap` | `academic-cap` | Education |
| `fa-file` | `document-text` | Documents |
| `fa-folder` | `folder` | Folders |
| `fa-briefcase` | `briefcase` | Business |
| `fa-cog` | `cog-6-tooth` | Settings |
| `fa-sign-out` | `logout` | Logout |
| `fa-lock` | `lock-closed` | Security |

## 🔍 Finding More Icons

1. Go to [heroicons.com](https://heroicons.com)
2. Search for what you need (e.g., "calendar", "bell", "search")
3. Click the icon
4. Copy the SVG code
5. Use it directly or add to the component

## 💡 Pro Tips

1. **Size consistency**: Use the same size for similar icons (e.g., all nav icons `w-5 h-5`)
2. **Color inheritance**: Icons inherit text color, so use `text-blue-500` classes
3. **Hover effects**: Add `hover:text-blue-600` for interactive icons
4. **Dark mode**: Use `dark:text-gray-300` for dark mode support

## 🚀 Quick Reference

```blade
{{-- Basic --}}
<x-heroicon name="icon-name" />

{{-- Custom size --}}
<x-heroicon name="icon-name" class="w-6 h-6" />

{{-- Solid variant --}}
<x-heroicon name="icon-name" variant="solid" />

{{-- With styling --}}
<x-heroicon name="icon-name" class="w-5 h-5 text-blue-500 hover:text-blue-700" />
```

That's it! You're ready to use Heroicons throughout your Laravel app! 🎉

