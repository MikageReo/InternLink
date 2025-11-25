# 🎨 Heroicons Guide for Laravel

This guide shows you how to use Heroicons in your Laravel Blade templates.

## 📚 What are Heroicons?

Heroicons are beautiful, hand-crafted SVG icons from the makers of Tailwind CSS. They come in three variants:
- **Outline** (default) - Clean, minimal icons with strokes
- **Solid** - Filled icons for emphasis
- **Mini** - Smaller, compact versions

## 🚀 Quick Start

### Method 1: Using the Heroicon Component (Recommended)

We've created a reusable component for you! Use it like this:

```blade
{{-- Basic usage --}}
<x-heroicon name="home" />

{{-- With custom size --}}
<x-heroicon name="user" class="w-6 h-6" />

{{-- Solid variant --}}
<x-heroicon name="academic-cap" variant="solid" />

{{-- Mini variant --}}
<x-heroicon name="users" variant="mini" class="w-4 h-4" />

{{-- With additional attributes --}}
<x-heroicon name="document-text" class="text-blue-500 hover:text-blue-700" />
```

### Method 2: Direct SVG (For Custom Icons)

If you need an icon that's not in the component, you can copy the SVG directly from [heroicons.com](https://heroicons.com):

```blade
{{-- Example: Home icon (outline) --}}
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
</svg>
```

## 📖 Common Icons Available in Component

The component includes these commonly used icons:

- `home` - Home/Dashboard
- `user` - Single user
- `users` - Multiple users
- `academic-cap` - Education/University
- `document-text` - Documents
- `folder` - Folders/Files
- `briefcase` - Business/Work
- `cog-6-tooth` - Settings
- `arrow-down` - Down arrow
- `arrow-up` - Up arrow
- `x-mark` - Close/Delete
- `bars-3` - Menu/Hamburger
- `logout` - Logout
- `user-circle` - User profile
- `lock-closed` - Security/Password
- `clipboard-document-list` - Checklist/Verification
- `building-office` - Company/Organization

## 💡 Examples

### Navigation Links

```blade
{{-- Before (using FontAwesome) --}}
<i class="fas fa-home mr-2"></i> Dashboard

{{-- After (using Heroicons) --}}
<x-heroicon name="home" class="w-4 h-4 mr-2" /> Dashboard
```

### Buttons

```blade
<button class="flex items-center px-4 py-2 bg-blue-600 text-white rounded">
    <x-heroicon name="user" class="w-5 h-5 mr-2" />
    Add User
</button>
```

### Dropdown Menus

```blade
<div class="flex items-center">
    <span>Menu</span>
    <x-heroicon name="arrow-down" class="w-4 h-4 ml-1" />
</div>
```

### Cards with Icons

```blade
<div class="flex items-center">
    <div class="bg-blue-100 rounded p-3">
        <x-heroicon name="academic-cap" class="w-6 h-6 text-blue-600" />
    </div>
    <div class="ml-4">
        <h3>Students</h3>
        <p>Total: 150</p>
    </div>
</div>
```

## 🎨 Styling Icons

Heroicons inherit text color, so you can style them easily:

```blade
{{-- Change color --}}
<x-heroicon name="user" class="w-5 h-5 text-blue-500" />

{{-- Hover effects --}}
<x-heroicon name="home" class="w-5 h-5 text-gray-500 hover:text-blue-600" />

{{-- Dark mode support --}}
<x-heroicon name="document" class="w-5 h-5 text-gray-700 dark:text-gray-300" />
```

## 📏 Size Classes

Common Tailwind size classes:
- `w-4 h-4` - Small (16px)
- `w-5 h-5` - Default (20px)
- `w-6 h-6` - Medium (24px)
- `w-8 h-8` - Large (32px)
- `w-10 h-10` - Extra Large (40px)

## 🔍 Finding More Icons

1. Visit [heroicons.com](https://heroicons.com)
2. Search for the icon you need
3. Click on it to see the SVG code
4. Copy the `<path>` content
5. Add it to the component or use directly

## 🛠️ Adding New Icons to Component

To add a new icon to the component, edit `resources/views/components/heroicon.blade.php`:

1. Find the `$icons` array
2. Add your icon's path data:

```php
'outline' => [
    // ... existing icons
    'your-icon-name' => '<path ... />',
],
```

## 📝 Differences Between Variants

### Outline (Default)
- Uses `stroke` instead of `fill`
- Has `stroke-width="1.5"`
- Best for: Navigation, buttons, general UI

### Solid
- Uses `fill` instead of `stroke`
- More prominent
- Best for: Emphasis, important actions

### Mini
- Smaller viewBox (20x20 instead of 24x24)
- Compact design
- Best for: Dense UIs, mobile interfaces

## ✅ Best Practices

1. **Consistency**: Use the same variant throughout your app
2. **Size**: Keep icon sizes consistent (e.g., all nav icons `w-5 h-5`)
3. **Color**: Use semantic colors (blue for info, red for danger, etc.)
4. **Accessibility**: Add `aria-label` when icons are standalone:

```blade
<button aria-label="Close">
    <x-heroicon name="x-mark" class="w-5 h-5" />
</button>
```

## 🎯 Quick Reference

```blade
{{-- Basic --}}
<x-heroicon name="icon-name" />

{{-- Custom size --}}
<x-heroicon name="icon-name" class="w-6 h-6" />

{{-- Solid variant --}}
<x-heroicon name="icon-name" variant="solid" />

{{-- With color --}}
<x-heroicon name="icon-name" class="w-5 h-5 text-blue-500" />
```

Happy icon-ing! 🎨

