<?php

namespace App\Orchid\Screens\Settings;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class AttachmentCleanupScreen extends Screen
{
    private const KEY_DAYS = 'attachments.cleanup.days';
    private const DEFAULT_DAYS = 7; // vaikimisi 7 päeva - 7 päeva vanuseid asju saab eemaldada


    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $days = (int) Setting::get(self::KEY_DAYS, self::DEFAULT_DAYS);
        $days = max(1, $days);

        $orphansQuery = Attachment::query()
            ->where('disk', 'public')
            ->where('created_at', '<', now()->subDays($days))
            ->whereNotIn('id', function($q) {
                $q->select('attachment_id')->from('attachmentable');
            })
            ->whereNotExists(function($q) {
                $q->selectRaw('1')->from('posts')
                ->whereRaw("posts.body_html LIKE CONCAT('%', '/storage/', attachments.path, attachments.name, '.', attachments.extension, '%')");
            })
            ->orderByDesc('created_at');

        return [
            'settings' => [
                'attachments' => [
                    'cleanup' => [
                        'days' => $days,
                    ],
                ],
            ],
            'days' => $days,
            'orphansCount' => (clone $orphansQuery)->count(),
            'orphans' => $orphansQuery->paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Piltide puhastus';
    }

    public function permission(): ?iterable
    {
        return ['platform.settings.attachments'];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Salvesta seaded')
                ->type(Color::SUCCESS)
                ->method('saveSettings'),

            Button::make('Kustuta kasutamata failid')
                ->type(Color::DANGER())
                ->confirm('Kustutatakse ainult seoseta failid.')
                ->method('cleanup'),    
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('settings.attachments.cleanup.days')
                    ->title('Päevade piir (turvavaru')
                    ->type('number')
                    ->min(1)
                    ->help('Salvestab settings tabelisse- Soovitus 7 või 14- Kustutatakse ainult vanemad kui x päeva.'),


                Input::make('orphansCount')   
                    ->title('Kustutamata kirjeid hetkel')
                    ->readonly()
                    ->value(fn ($data) => $data ?? 0)
                    ->help('Need kirjed pole seotud ühegi postitusega.'), 

            ]),

            Layout::table('orphans', [
                TD::make('id', 'ID')->sort(),
                TD::make('original_name', 'Fail')->render(fn (Attachment $att) => $att->original_name ?? $att->name),
                TD::make('mime', 'MIME')->render(fn (Attachment $att) => $att->mime),
                TD::make('size', 'Suurus')->render (fn (Attachment $att) => $att->size),
                TD::make('path', 'Path')->render (fn (Attachment $att) => $att->path),
                TD::make('created_at', 'Lisatud')
                    ->sort()
                    ->render(fn (Attachment $att) => optional($att->created_at)->format('d.m.Y H:i:s')),

         ])->title('Kustutamisele minevad failid.')
        ];
    }

    public function saveSettings(Request $request) {
        $days = (int) data_get($request->all(), 'settings.attachments.cleanup.days', self::DEFAULT_DAYS);
        $days = max(1, $days);

        Setting::set(self::KEY_DAYS, (string) $days);

        Toast::info("Salvestatud. Päevade piir {$days}.");
    }

    public function cleanup(Request $request) {
        $daysFromForm = data_get($request->all(), 'settings.attachments.cleanup.days', null);
        $days = is_null($daysFromForm)
            ? (int) Setting::get(self::KEY_DAYS, self::DEFAULT_DAYS) // True
            : (int) $daysFromForm; // False

        $days = max(1, $days);

        $orphans = Attachment::query()
            ->where('disk', 'public')
            ->where('created_at', '<', now()->subDays($days))
            ->whereNotIn('id', function($q) {
                $q->select('attachment_id')->from('attachmentable');
            })
            ->whereNotExists(function($q) {
                $q->selectRaw('1')->from('posts')
                ->whereRaw("posts.body_html LIKE CONCAT('%', '/storage/', attachments.path, attachments.name, '.', attachments.extension, '%')");
            })
            ->get();

        // Algseaded    
        $deletedDB = 0;
        $deletedFiles = 0;
        $missingFiles = 0;    

        foreach($orphans as $att) {
            // Õige failitee: path + name + extension
            $dir = trim((string) $att->path, '/'); // nt "2026/02/18"
            $file = (string) $att->name; // nt "2ff6vd"
            $ext = (string) $att->extension; // nt "webp"

            $relativePath = $dir !== ''
                ? $dir . '/' . $file . '.' . $ext // 2026/02/18/2ff6vd.webp
                : $file . '.' . $ext; // 2ff6vd.webp

            if($relativePath !== '' && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
                $deletedFiles++;
            } else {
                $missingFiles++;
            }

            $att->delete();
            $deletedDB++;

        }
        Toast::info("Puhastus tehtud. DB kirjeid: {$deletedDB}. Faile kustutatud {$deletedFiles}. Puuduvaid faile: {$missingFiles}.");

        return redirect()->route('platform.settings.attachments');
        
    }
}
