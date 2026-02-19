<?php

namespace App\Orchid\Layouts\Comment;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use App\Models\Comment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Modal;
use Orchid\Support\Color;

class CommentListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'comments';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('created_at', 'Loodud')
                ->sort()
                ->render(fn (Comment $c)=> optional($c->created_at)->format('d.m.Y H:i:s')),
            TD::make('user', 'Autor')
                ->render(function (Comment $c) {
                    $name = $c->user?->name ?? 'Tundmatu';
                    return e($name);
                }),
            TD::make('post', 'Postitus')
                ->render(function (Comment $c) {
                    if(! $c->post) {
                        return '<span class="text-muted">Puudub</span>';
                    }

                    $url = route('posts.show', $c->post);
                    return '<a href="'.e($url).'" target="_blank">'.e($c->post->title).'</a>';

                }),
            TD::make('comment', 'Kommentaar')
                ->render(function (Comment $c){
                    $text = (string) $c->comment;
                    $short = mb_strlen($text) > 120 ? (mb_substr($text, 0, 120) . '...') : $text;
                    return e($short);
                })
                ->filter(TD::FILTER_TEXT),
                
            TD::make('action', 'Tegevused')
    ->alignLeft()
    ->render(function(Comment $c){

        $toggleIcon = $c->is_hidden ? 'bs.eye' : 'bs.eye-slash';

        $text = (string) $c->comment;
        $isLong = mb_strlen($text) > 120;

        $viewButton = '';

        if ($isLong) {
    $viewButton = '
        <button 
                    type="button"
                    class="btn btn-info btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#commentModal'.$c->id.'"
                >
                    <i class="bi bi-card-text"></i>
                </button>

        <div class="modal fade" id="commentModal'.$c->id.'" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kommentaar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        '.e($c->comment).'
                    </div>
                </div>
            </div>
        </div>
    ';


        }

        return
            '<div class="d-flex align-items-center gap-2">'
            .
            Button::make()
                ->icon($toggleIcon)
                ->class('btn text-primary')
                ->method('toggleHidden', ['comment' => $c->id])
                ->confirm('Kas oled kindel, et soovid muuta?')
                ->render()

            .

            Button::make()
                ->icon('bs.trash')
                ->class('btn text-danger')
                ->method('remove', ['comment' => $c->id])
                ->confirm('Kustutamine on lõplik. Kas kustutada?')
                ->render()

            .

            $viewButton

            .

            '</div>';
    }),

      
        ];
    }
}
