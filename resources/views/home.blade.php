@extends('layouts.app')

@section('content')
@php
    $fmt = fn ($dt) => $dt ? $dt->format('d.m.Y H:i:s') : '—';
@endphp

<div class="container">
    {{-- <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Dashboard</div>
                <div class="card-body">
                    Oled sisse logitud.
                </div>
            </div>
        </div>
    </div> --}}

    {{-- Minu tegevus --}}
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-3">Minu tegevus</h4>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header">Kommentaarid</div>
                <div class="card-body">
                    <div>Kokku: <strong>{{ $userStats['comments']['total'] }}</strong></div>
                    <div>Nähtavad: <strong>{{ $userStats['comments']['visible'] }}</strong></div>
                    <div>Peidetud: <strong>{{ $userStats['comments']['hidden'] }}</strong></div>
                    <hr>
                    <div>Esimene: <strong>{{ $fmt($userStats['comments']['first_at']) }}</strong></div>
                    <div>Viimane: <strong>{{ $fmt($userStats['comments']['last_at']) }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header">Reaktsioonid</div>
                <div class="card-body">
                    <div>Kokku: <strong>{{ $userStats['reactions']['total'] }}</strong></div>
                    <hr>
                    <div>{{ $userStats['reactions']['labels'][1] }}: <strong>{{ $userStats['reactions']['by_value'][1] }}</strong></div>
                    <div>{{ $userStats['reactions']['labels'][2] }}: <strong>{{ $userStats['reactions']['by_value'][2] }}</strong></div>
                    <div>{{ $userStats['reactions']['labels'][3] }}: <strong>{{ $userStats['reactions']['by_value'][3] }}</strong></div>
                    <hr>
                    <div>Esimene: <strong>{{ $fmt($userStats['reactions']['first_at']) }}</strong></div>
                    <div>Viimane: <strong>{{ $fmt($userStats['reactions']['last_at']) }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header">Kiirülevaade</div>
                <div class="card-body">
                    <div>Viimane kommentaar: <strong>{{ $fmt($userStats['comments']['last_at']) }}</strong></div>
                    <div>Viimane reaktsioon: <strong>{{ $fmt($userStats['reactions']['last_at']) }}</strong></div>
                    <hr>
                    @if($isAdmin)
                        <div class="alert alert-info mb-0">
                            Sul on admin õigused. Allpool on lisastatistika.
                        </div>
                    @else
                        <div class="text-muted">
                            Admin-paneel asub menüüs, kui sul on ligipääs.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Viimased tegevused --}}
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">Viimased kommentaarid</div>
                <div class="card-body p-0">
                    @if($userStats['comments']['recent']->isEmpty())
                        <div class="p-3 text-muted">Kommentaare pole.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Postitus</th>
                                        <th class="text-end">Aeg</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userStats['comments']['recent'] as $c)
                                        <tr>
                                            <td>
                                                @if($c->post)
                                                    <a href="{{ route('posts.show', $c->post) }}">{{ $c->post->title }}</a>
                                                @else
                                                    <span class="text-muted">Postitus puudub</span>
                                                @endif
                                                @if($c->is_hidden)
                                                    <span class="badge bg-secondary ms-2">Peidetud</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ $c->created_at?->format('d.m.Y H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">Viimased reaktsioonid</div>
                <div class="card-body p-0">
                    @if($userStats['reactions']['recent']->isEmpty())
                        <div class="p-3 text-muted">Reaktsioone pole.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Postitus</th>
                                        <th>Reaktsioon</th>
                                        <th class="text-end">Aeg</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userStats['reactions']['recent'] as $r)
                                        <tr>
                                            <td>
                                                @if($r->post)
                                                    <a href="{{ route('posts.show', $r->post) }}">{{ $r->post->title }}</a>
                                                @else
                                                    <span class="text-muted">Postitus puudub</span>
                                                @endif
                                            </td>
                                            <td>{{ $userStats['reactions']['labels'][$r->value] ?? $r->value }}</td>
                                            <td class="text-end">{{ $r->created_at?->format('d.m.Y H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Admin statistika --}}
    @if($isAdmin && $adminStats)
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="mb-3">Sisu ülevaade (admin)</h4>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">Postitused</div>
                    <div class="card-body">
                        <div>Kokku: <strong>{{ $adminStats['posts']['total'] }}</strong></div>
                        <div>Avaldatud: <strong>{{ $adminStats['posts']['published'] }}</strong></div>
                        <div>Ajastatud: <strong>{{ $adminStats['posts']['scheduled'] }}</strong></div>
                        <div>Mustandid: <strong>{{ $adminStats['posts']['draft'] }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">Kommentaarid (kõik)</div>
                    <div class="card-body">
                        <div>Kokku: <strong>{{ $adminStats['comments']['total'] }}</strong></div>
                        <div>Nähtavad: <strong>{{ $adminStats['comments']['visible'] }}</strong></div>
                        <div>Peidetud: <strong>{{ $adminStats['comments']['hidden'] }}</strong></div>
                        <hr>
                        <div>Viimane: <strong>{{ $fmt($adminStats['comments']['last_at']) }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">Reaktsioonid (kõik)</div>
                    <div class="card-body">
                        <div>Kokku: <strong>{{ $adminStats['reactions']['total'] }}</strong></div>
                        <hr>
                        <div>{{ $adminStats['reactions']['labels'][1] }}: <strong>{{ $adminStats['reactions']['by_value'][1] }}</strong></div>
                        <div>{{ $adminStats['reactions']['labels'][2] }}: <strong>{{ $adminStats['reactions']['by_value'][2] }}</strong></div>
                        <div>{{ $adminStats['reactions']['labels'][3] }}: <strong>{{ $adminStats['reactions']['by_value'][3] }}</strong></div>
                        <hr>
                        <div>Viimane: <strong>{{ $fmt($adminStats['reactions']['last_at']) }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">Todo tegevused</div>
                    <div class="card-body">
                        <div>Kokku todosid: <strong>{{ $adminStats['todos']['total'] }}</strong></div>
                        <div>Tehtud todod: <strong>{{ $adminStats['todos']['done'] }}</strong></div>
                        <div>Veel tegemata todod: <strong>{{ $adminStats['todos']['not_done'] }}</strong></div>
                        <div>Järgmine todo teha: <strong>{{ $fmt($adminStats['todos']['due_at']) }}</strong></div>
                        <div>Viimati loodud todo: <strong>{{ $fmt($adminStats['todos']['created_at']) }}</strong></div>

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
