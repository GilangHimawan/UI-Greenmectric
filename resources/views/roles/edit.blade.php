@extends('adminlte::page')

@section('title','Edit Role')

@section('content_header')
<h1>Edit Role</h1>
@stop

@section('content')

<div class="card card-success">

    <form action="{{ route('roles.update', $role->id) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="card-body">

            @if($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            <div class="form-group">

                <label>Nama Role</label>

                <input
                    type="text"
                    name="name"
                    value="{{ $role->name }}"
                    class="form-control">

            </div>

            <hr>

            <div class="mb-3">

                <div class="custom-control custom-checkbox">

                    <input
                        type="checkbox"
                        class="custom-control-input"
                        id="checkAll">

                    <label
                        class="custom-control-label"
                        for="checkAll">

                        Pilih Semua Permission

                    </label>

                </div>

            </div>

            <div class="row">

                @foreach($permissions as $module => $items)

                    <div class="col-md-4">

                        <div class="card card-outline card-success">

                            <div class="card-header">

                                <strong>

                                    {{ ucfirst($module) }}

                                </strong>

                            </div>

                            <div class="card-body">

                                @foreach($items as $permission)

                                    <div class="custom-control custom-checkbox mb-2">

                                        <input
                                            type="checkbox"
                                            class="custom-control-input permission"
                                            id="{{ $permission->id }}"
                                            name="permission[]"
                                            value="{{ $permission->name }}"
                                            {{ in_array($permission->name,$rolePermissions) ? 'checked' : '' }}>
                                        <label
                                            class="custom-control-label"
                                            for="{{ $permission->id }}">

                                            {{ ucfirst(last(explode('.',$permission->name))) }}

                                        </label>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

        <div class="card-footer">

            <button class="btn btn-success">

                <i class="fas fa-save"></i>

                Simpan

            </button>

            <a
                href="{{ route('roles.index') }}"
                class="btn btn-secondary">

                Kembali

            </a>

        </div>

    </form>

</div>

@stop

@section('js')

<script>

$('#checkAll').click(function(){

    $('.permission').prop('checked',$(this).prop('checked'));

});

</script>

@stop