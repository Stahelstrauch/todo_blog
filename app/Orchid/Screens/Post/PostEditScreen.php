<?php

namespace App\Orchid\Screens\Post;

use App\Models\Post;
use App\Models\User;
use App\Notifications\PostPublished;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PostEditScreen extends Screen
{
    public ?Post $post = null;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(?Post $post = null): iterable
    {
        $post = $post ?? new Post();
        $this->post = $post;

        return ['post' => $post];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return ($this->post && $this->post->exists) ? 'Muuda postitust' : 'Loo postitus';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        $exists = $this->post && $this->post->exists;
        return [
            Button::make('Salvesta')
                ->type(Color::PRIMARY())
                ->method('save'),

            Button::make('Kustuta')
                ->type(Color::DANGER())
                ->confirm('Kas soovite postituse kustutada?')
                ->canSee($exists)
                ->method('remove'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {

        $layouts = [];
        
        //Eelvaade ainult muutmisvaates ja ainult siis kui on featured_image_path olemas
        if($this->post && $this->post->exists && !empty($this->post->featured_image_path)) {
            $layouts[] = Layout::legend('post', [
                Sight::make('featured_image_path', 'Praegune pilt')
                    ->render(fn(Post $p) => '<img src="'.$p->featuredImageUrl().'" style="max-width:420px;width:100%;height:auto;border-radius:8px>"')
            ]);
        }

        // Kogu vorm
        $layouts[] = Layout::rows([
            Input::make('post.title')
                ->title(new HtmlString('<b>Pealkiri</b>'))
                ->required()
                ->maxlength(200),

            Input::make('post.slug')
                ->title(new HtmlString('<b>Slug</b>'))
                ->maxlength(220)
                ->help('Peab olema unikaalne. Kui jääb tühjaks, siis genereeritakse ise.'),

            Cropper::make('post.featured_image_path')
                ->title(new HtmlString('<b>Postituse päise pilt</b>'))
                ->targetRelativeUrl()
                ->help('Valikuline. Kui ei lisa, kasutatakse vaikimisi pilti.'),
                
            TextArea::make('post.intro')
                ->title(new HtmlString('<b>Sissejuhatus</b>'))
                ->rows(4)
                ->maxlenght(200),

            Quill::make('post.body_html')
                ->title(new HtmlString('<b>Postituse sisu</b>'))
                ->required(),
                // ->toolbar(["text", "color", "header", "list", "format", "media"]),


            DateTimer::make('post.published_at')    
                ->title(new HtmlString('<b>Avaldamise aeg</b>'))
                ->allowInput()
                ->format('Y-m-d H:i:s')
                ->help('Mustandi jaoks jäta väli tühjaks.')
        ]);
    
        return $layouts;
    }

    private function makeUniqueSlug(string $slug, ?int $ignoredId = null) {
        $base = $slug;
        $i = 2;

        while(
            Post::query()
                ->when($ignoredId, fn($q) => $q->where('id', '!=', $ignoredId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function processFeaturedImage(?string $new, ?string $old, string $slug ): ?string {
        if(empty($new)) {
            $this->deleteFeaturedIfExists($old);
            return null;
        }

        $newRel = $this->toPublicDiskRelative($new); // "2026/01/29../../x.jpg"
        $oldRel = $this->toPublicDiskRelative($old); // "posts/..."

        // Kui sisuliselt sama tee ei muuda midagi
        if(!empty($oldRel) && $newRel === $oldRel) {
            return $oldRel;
        }

        // Normaliseeritud failinimi slugist
        $base = Str::slug($slug) ?: 'post';
        $ext = pathinfo($newRel, PATHINFO_EXTENSION) ?: 'jpg';

        $filename = $base . '-' . Str::lower(Str::random(6)) . '-' . strtolower($ext);
        $dest = 'posts/' . $filename;

        // Liiguta public diskil (storage/app/public)
        if(Storage::disk('public')->exists($newRel)) {
            Storage::disk('public')->move($newRel, $dest);
        } else {
            // Kui faili pole (harva), siis v'hemalt ära lõhu süsteemi ja jäta "posts/..." määramata
            return $oldRel;
        }
        // DB hoia ainult "posts/..."
        return $dest;
    }

    private function deleteFeaturedIfExists(?string $path) {
        if(empty($path)) {
            return;
        }

        $rel = $this->toPublicDiskRelative($path);
        if(!empty($rel)) {
            Storage::disk('public')->delete($rel);
        }
    }

    private function toPublicDiskRelative(?string $path) {
        if(empty($path)) {
            return;
        }

        $p = ltrim($path, '/');

        if(str_starts_with($p, 'storage/')) {
            $p = substr($p, strlen('storage/'));
        }
        return $p;
    }

    public function remove(Post $post) {
        $this->deleteFeaturedIfExists($post->featured_image_path);
        $post->delete();

        Toast::info('Postitus kustutatud!');
        return redirect()->route('platform.posts');
    }

    public function save(Post $post, Request $request){
        $validated = $request->validate([
            'post.title' => ['required', 'string', 'max:200'],
            'post.slug' => ['nullable', 'string', 'max:220'],
            'post.featured_image_path' => ['nullable', 'string', 'max:255'],
            'post.intro' => ['nullable', 'string', 'max:200'],
            'post.body_html' => ['required', 'string'],
            'post.published_at' => ['nullable', 'date'],
        ]);

        $data = $validated['post'];

        $oldPublishedAt = $post->published_at;

        // Uute postituste jaoks salvesta Quill pildid suhtelise URL-na (storage/)
        // Näiteks "http://localhost/storage/" => "storage/..."
        if(!empty($data['body_html'])) {
            $data['body_html'] = preg_replace(
                '#(<img\b[^>]*\bsrc=")https?://[^/]+(/storage/[^"]+)(")#i',
                '$1$2$3',
                $data['body_html']
            );
        }

        $oldFeatured = $post->featured_image_path;
        $newFeatured = $data['featured_image_path'] ?? null;

        // Ära salvesta ajutist teed otse DB-sse
        unset($data['featured_image_path']);

        // 1. võta slug sisendist või genereeri title põhjal
        $rawSlug = trim((string) ($data['slug'] ?? ''));
        $base = $rawSlug !== '' ? $rawSlug : $data['title'];

        // 2. normaliseeri slug
        $slug = Str::slug($base);
        if($slug === '') {
            $slug = 'post';
        }

        // 3. Tee unikaalseks
        $slug = $this->makeUniqueSlug($slug, $post->id);
        $data['slug'] = $slug;

        $post->fill($data);

        if(!$post->exists) {
            $post->user_id = $request->user()->id;
        }

        $post->save();

        // Featured pilt: liigutamine, normaliseerimine + vana kustutus
        $finalpath = $this->processFeaturedImage($newFeatured, $oldFeatured, $post->slug);

        if($finalpath !== $oldFeatured) {
            $post->featured_image_path = $finalpath; // "posts/.." või null
            $post->save();
        }

        // Notification asjad
        $newPublishedAt = $post->published_at;

        $justBecomePublished = empty($oldPublishedAt)
            && !empty($newPublishedAt)
            && $newPublishedAt <= now();

        if($justBecomePublished) {
            $publisher = $request->user();

            User::query()
                ->whereKeyNot($publisher->id)
                ->each(function ($user) use ($post, $publisher){
                    $user->notify(new PostPublished(
                        $post,
                        $publisher->name ?? $publisher->email));
                });    
        }

        Toast::info('Postitus salvestatud!');
        return redirect()->route('platform.posts');
    }
}
