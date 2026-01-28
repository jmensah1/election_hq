# Elections HQ - Styling Guide

**UI Stack:** Tailwind CSS 3.x + Livewire 3.x + Filament 3.x + Alpine.js  
**Design Philosophy:** Clean, professional, mobile-first, accessible

---

## Color Palette

### Primary Colors (Election Theme)

```css
/* Tailwind config extension */
colors: {
    primary: {
        50: '#eff6ff',
        100: '#dbeafe',
        200: '#bfdbfe',
        300: '#93c5fd',
        400: '#60a5fa',
        500: '#3b82f6',  /* Primary blue */
        600: '#2563eb',
        700: '#1d4ed8',
        800: '#1e40af',
        900: '#1e3a8a',
    },
    success: {
        500: '#22c55e',  /* Vote confirmed */
        600: '#16a34a',
    },
    danger: {
        500: '#ef4444',  /* Errors, warnings */
        600: '#dc2626',
    },
    neutral: {
        50: '#fafafa',
        100: '#f4f4f5',
        200: '#e4e4e7',
        300: '#d4d4d8',
        400: '#a1a1aa',
        500: '#71717a',
        600: '#52525b',
        700: '#3f3f46',
        800: '#27272a',
        900: '#18181b',
    }
}
```

### Usage

| Element | Color | Class |
|---------|-------|-------|
| Primary buttons | Blue 600 | `bg-primary-600 hover:bg-primary-700` |
| Success states | Green 500 | `bg-success-500 text-white` |
| Error states | Red 500 | `bg-danger-500 text-white` |
| Body text | Neutral 700 | `text-neutral-700` |
| Headings | Neutral 900 | `text-neutral-900` |
| Backgrounds | Neutral 50/100 | `bg-neutral-50` |
| Borders | Neutral 200 | `border-neutral-200` |

---

## Typography

### Font Stack

```css
/* tailwind.config.js */
fontFamily: {
    sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
}
```

Install Inter font:
```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```

### Text Sizes

| Element | Class | Size |
|---------|-------|------|
| Page title | `text-3xl font-bold` | 30px |
| Section heading | `text-xl font-semibold` | 20px |
| Card title | `text-lg font-medium` | 18px |
| Body text | `text-base` | 16px |
| Small text | `text-sm` | 14px |
| Caption | `text-xs text-neutral-500` | 12px |

---

## Layout Components

### Page Container

```html
<div class="min-h-screen bg-neutral-50">
    <div class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <!-- Content -->
    </div>
</div>
```

### Card Component

```html
<div class="bg-white rounded-lg shadow-sm border border-neutral-200 p-6">
    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Card Title</h2>
    <p class="text-neutral-600">Card content goes here.</p>
</div>
```

### Section Spacing

```html
<!-- Between sections -->
<div class="space-y-8">
    <section>...</section>
    <section>...</section>
</div>

<!-- Within sections -->
<div class="space-y-4">
    <element>...</element>
    <element>...</element>
</div>
```

---

## Voter Portal Components

### Login Page

```html
<div class="min-h-screen flex items-center justify-center bg-neutral-100 px-4">
    <div class="max-w-md w-full">
        <!-- Organization Logo -->
        <div class="text-center mb-8">
            <img src="{{ $organization->logo_url }}" alt="" class="h-16 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-neutral-900">{{ $organization->name }}</h1>
            <p class="text-neutral-600 mt-2">Sign in to vote</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <a href="{{ route('auth.google') }}" 
               class="w-full flex items-center justify-center gap-3 bg-white border-2 border-neutral-300 rounded-lg px-6 py-3 text-neutral-700 font-medium hover:bg-neutral-50 hover:border-neutral-400 transition-colors">
                <!-- Google Icon SVG -->
                <svg class="w-5 h-5" viewBox="0 0 24 24">...</svg>
                Sign in with Google
            </a>
            
            <p class="text-sm text-neutral-500 text-center mt-6">
                Only registered voters can sign in
            </p>
        </div>
    </div>
</div>
```

### Election Card

```html
<div class="bg-white rounded-lg shadow-sm border border-neutral-200 overflow-hidden hover:shadow-md transition-shadow">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-neutral-900">{{ $election->title }}</h3>
                <p class="text-sm text-neutral-500 mt-1">{{ $election->positions_count }} positions</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Open
            </span>
        </div>
        
        <p class="text-neutral-600 mt-3">{{ Str::limit($election->description, 120) }}</p>
        
        <div class="flex items-center gap-4 mt-4 text-sm text-neutral-500">
            <span>Closes: {{ $election->voting_end_date->format('M j, g:i A') }}</span>
        </div>
    </div>
    
    <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200">
        <a href="{{ route('voter.vote', $election) }}" 
           class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
            Vote Now
        </a>
    </div>
</div>
```

### Candidate Selection Card

```html
<label class="block cursor-pointer">
    <input type="radio" name="votes[{{ $position->id }}]" value="{{ $candidate->id }}" 
           class="peer sr-only" required>
    
    <div class="bg-white border-2 border-neutral-200 rounded-xl p-4 
                peer-checked:border-primary-500 peer-checked:bg-primary-50
                hover:border-neutral-300 transition-colors">
        <div class="flex items-start gap-4">
            <!-- Candidate Photo -->
            <img src="{{ $candidate->photo_url }}" alt="" 
                 class="w-16 h-16 rounded-full object-cover border-2 border-neutral-200">
            
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h4 class="font-semibold text-neutral-900">{{ $candidate->user->name }}</h4>
                    <!-- Checkmark (shown when selected) -->
                    <svg class="w-5 h-5 text-primary-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-sm text-neutral-500">{{ $candidate->voter_id }}</p>
                
                @if($candidate->manifesto)
                <p class="text-sm text-neutral-600 mt-2 line-clamp-2">{{ $candidate->manifesto }}</p>
                @endif
            </div>
        </div>
    </div>
</label>
```

### Progress Indicator (Voting Steps)

```html
<div class="flex items-center justify-center mb-8">
    @foreach($positions as $index => $position)
        <div class="flex items-center">
            <!-- Step Circle -->
            <div class="flex items-center justify-center w-8 h-8 rounded-full 
                        {{ $currentStep > $index ? 'bg-primary-600 text-white' : 
                           ($currentStep === $index ? 'bg-primary-600 text-white ring-4 ring-primary-100' : 
                           'bg-neutral-200 text-neutral-500') }}">
                @if($currentStep > $index)
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                @else
                    {{ $index + 1 }}
                @endif
            </div>
            
            <!-- Connector Line -->
            @if(!$loop->last)
                <div class="w-12 h-0.5 {{ $currentStep > $index ? 'bg-primary-600' : 'bg-neutral-200' }}"></div>
            @endif
        </div>
    @endforeach
</div>
```

### Vote Confirmation Page

```html
<div class="min-h-screen bg-neutral-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
        <!-- Success Icon -->
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-neutral-900 mb-2">Vote Submitted!</h1>
        <p class="text-neutral-600 mb-6">Your vote has been recorded successfully.</p>
        
        <div class="bg-white rounded-lg shadow-sm border border-neutral-200 p-6 text-left mb-6">
            <h2 class="font-semibold text-neutral-900 mb-3">You voted for:</h2>
            <ul class="space-y-2 text-neutral-600">
                @foreach($votedPositions as $position)
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        {{ $position->name }}
                    </li>
                @endforeach
            </ul>
            <p class="text-sm text-neutral-500 mt-4">
                Voted at {{ now()->format('M j, Y \a\t g:i A') }}
            </p>
        </div>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full px-6 py-3 bg-neutral-800 text-white font-medium rounded-lg hover:bg-neutral-900 transition-colors">
                Sign Out
            </button>
        </form>
    </div>
</div>
```

---

## Buttons

### Primary Button

```html
<button class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
    Submit Vote
</button>
```

### Secondary Button

```html
<button class="inline-flex items-center justify-center px-6 py-3 bg-white text-neutral-700 font-medium rounded-lg border border-neutral-300 hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500 transition-colors">
    Go Back
</button>
```

### Danger Button

```html
<button class="inline-flex items-center justify-center px-6 py-3 bg-danger-600 text-white font-medium rounded-lg hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500 transition-colors">
    Cancel
</button>
```

### Button with Loading State (Livewire)

```html
<button type="submit" 
        wire:loading.attr="disabled"
        wire:loading.class="opacity-50 cursor-not-allowed"
        class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
    <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span wire:loading.remove>Submit Vote</span>
    <span wire:loading>Submitting...</span>
</button>
```

---

## Form Elements

### Input Field

```html
<div>
    <label for="email" class="block text-sm font-medium text-neutral-700 mb-1">
        Email Address
    </label>
    <input type="email" id="email" name="email" required
           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
           placeholder="you@example.com">
    @error('email')
        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
    @enderror
</div>
```

### Select Dropdown

```html
<div>
    <label for="position" class="block text-sm font-medium text-neutral-700 mb-1">
        Position
    </label>
    <select id="position" name="position" required
            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors">
        <option value="">Select a position</option>
        @foreach($positions as $position)
            <option value="{{ $position->id }}">{{ $position->name }}</option>
        @endforeach
    </select>
</div>
```

---

## Alert Components

### Success Alert

```html
<div class="rounded-lg bg-green-50 border border-green-200 p-4">
    <div class="flex">
        <svg class="h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{{ $message }}</p>
        </div>
    </div>
</div>
```

### Error Alert

```html
<div class="rounded-lg bg-red-50 border border-red-200 p-4">
    <div class="flex">
        <svg class="h-5 w-5 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <div class="ml-3">
            <p class="text-sm font-medium text-red-800">{{ $message }}</p>
        </div>
    </div>
</div>
```

### Info Alert

```html
<div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
    <div class="flex">
        <svg class="h-5 w-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <div class="ml-3">
            <p class="text-sm font-medium text-blue-800">{{ $message }}</p>
        </div>
    </div>
</div>
```

---

## Results Display

### Results Card

```html
<div class="bg-white rounded-lg shadow-sm border border-neutral-200 overflow-hidden">
    <div class="px-6 py-4 bg-neutral-50 border-b border-neutral-200">
        <h3 class="font-semibold text-neutral-900">{{ $position->name }}</h3>
        <p class="text-sm text-neutral-500">{{ $position->total_votes }} total votes</p>
    </div>
    
    <div class="divide-y divide-neutral-100">
        @foreach($position->candidates->sortByDesc('vote_count') as $candidate)
            <div class="px-6 py-4 {{ $candidate->is_winner ? 'bg-green-50' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <img src="{{ $candidate->photo_url }}" alt="" 
                             class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <span class="font-medium text-neutral-900">{{ $candidate->user->name }}</span>
                            @if($candidate->is_winner)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Winner
                                </span>
                            @endif
                        </div>
                    </div>
                    <span class="font-semibold text-neutral-900">{{ $candidate->vote_count }} votes</span>
                </div>
                
                <!-- Progress Bar -->
                <div class="w-full bg-neutral-200 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $candidate->is_winner ? 'bg-green-500' : 'bg-primary-500' }}"
                         style="width: {{ $position->total_votes > 0 ? ($candidate->vote_count / $position->total_votes * 100) : 0 }}%">
                    </div>
                </div>
                <p class="text-sm text-neutral-500 mt-1">
                    {{ $position->total_votes > 0 ? number_format($candidate->vote_count / $position->total_votes * 100, 1) : 0 }}%
                </p>
            </div>
        @endforeach
    </div>
</div>
```

---

## Filament Admin Customization

### Custom Theme Colors

```php
// app/Providers/Filament/AdminPanelProvider.php
use Filament\Support\Colors\Color;

->colors([
    'primary' => Color::Blue,
    'danger' => Color::Red,
    'success' => Color::Green,
    'warning' => Color::Amber,
])
```

### Custom Branding

```php
->brandName('Elections HQ')
->brandLogo(asset('images/logo.svg'))
->favicon(asset('images/favicon.ico'))
```

---

## Responsive Breakpoints

Use Tailwind's default breakpoints:

| Breakpoint | Min Width | Usage |
|------------|-----------|-------|
| `sm:` | 640px | Large phones |
| `md:` | 768px | Tablets |
| `lg:` | 1024px | Laptops |
| `xl:` | 1280px | Desktops |

### Mobile-First Pattern

```html
<!-- Stack on mobile, side-by-side on tablet+ -->
<div class="flex flex-col md:flex-row gap-4">
    <div class="w-full md:w-1/2">Left</div>
    <div class="w-full md:w-1/2">Right</div>
</div>
```

---

## Accessibility Checklist

- [ ] All interactive elements have visible focus states (`focus:ring-2`)
- [ ] Color contrast meets WCAG AA (4.5:1 for text)
- [ ] Form inputs have associated labels
- [ ] Buttons have descriptive text (not just icons)
- [ ] Images have alt text
- [ ] Page has proper heading hierarchy (h1 → h2 → h3)
- [ ] Error messages are announced to screen readers

---

## Tailwind Config

```js
// tailwind.config.js
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./app/Filament/**/*.php",
        "./vendor/filament/**/*.blade.php",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
```

---

## Do NOT Use

- ❌ Bootstrap or any other CSS framework
- ❌ jQuery
- ❌ Custom CSS files (use Tailwind utilities)
- ❌ Inline styles
- ❌ !important overrides
- ❌ Pixel-based spacing (use Tailwind's scale)
- ❌ Hard-coded colors (use theme colors)
- ❌ Emojis in UI text (except for success checkmarks)
