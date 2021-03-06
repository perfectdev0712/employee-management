@extends('adminlte::page')

@section('title', 'Users List - Abacus N Brain')

@section('content_header')
    <h1>Users List</h1>
@stop

@section('content')

    <div class="card px-3 py-1">
        <div class="my-3">
            <a class="btn btn-success float-right" href="{{ route('auth.register') }}">+ Create New User</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Account</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        @if ($user->is_admin)
                            <tr>
                                <td>
                                    {{ $user->name }}
                                </td>
                                <td>
                                    {{ $user->email }}
                                </td>
                                <td>
                                    @if ($user->active)
                                        <span
                                            class="badge badge-success px-2 py-2"
                                            style="font-size: 1rem;">
                                            Active
                                        </span>
                                    @else
                                        <span
                                            class="badge badge-danger px-2 py-2"
                                            style="font-size: 1rem;">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            ACTIONS
                                        </a>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item text-primary" href="{{ route('auth.edit', ['id' => $user->id]) }}">Edit</a>
                                            @if(auth()->user()->id !== $user->id)
                                                <div class="dropdown-divider"></div>
                                                @if($user->active)
                                                    <a class="dropdown-item text-danger deactivateBtn"
                                                        href="{{ route('auth.destroy', ['id' => $user->id]) }}">
                                                        Deactivate
                                                    </a>
                                                @else
                                                    <a class="dropdown-item text-success activateBtn"
                                                        href="{{ route('auth.activate', ['id' => $user->id]) }}">
                                                        Activate
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@stop


@push('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('js')
    <script src="{{ asset('js/authactdeact.js') }}"></script>
@stop
