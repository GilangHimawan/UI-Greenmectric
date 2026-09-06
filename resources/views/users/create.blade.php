@extends('adminlte::page')

@section('title', 'Tambah User')

@section('content_header')
    <h1 class="m-0 text-dark"></h1>
@stop

@section('content')
 <div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body">
				<form action="{{ route('users.store') }}" method="post">
					@csrf
					<div class="form-group">
						<label for="name">Nama</label>
						<input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
						@error('name') <span class="text-danger">{{ $message }}</span> @enderror
					</div>
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
						<label for="username">Username</label>
						<input type="text"
							class="form-control @error('username') is-invalid @enderror"
							id="username" name="username"
							value="{{ old('username') }}" required>
						@error('username')
							<span class="text-danger">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label for="email">Email</label>
						<input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
						@error('email') <span class="text-danger">{{ $message }}</span> @enderror
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
						@error('password') <span class="text-danger">{{ $message }}</span> @enderror
					</div>
					<div class="form-group">
						<label for="password_confirmation">Konfirmasi Password</label>
						<input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
					</div>
					<div class="form-group">
						<label for="role">Role</label>
						<select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
							<option value="">Pilih role</option>
							@foreach($roles as $role)
								<option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
									{{ $role->name }}
								</option>
							@endforeach
						</select>
						@error('role') <span class="text-danger">{{ $message }}</span> @enderror
					</div>
					<div class="form-group">
						<label for="unit">Unit</label>
						<select class="form-control @error('unit') is-invalid @enderror" id="unit_id" name="unit_id" >
							<option value="">Pilih unit</option>
							@foreach($units as $u)
										<option value="{{ $u->id }}"
										{{ old('unit') == $u->id ? 'selected' : '' }}>
									{{ $u->nama_unit }}
								</option>
							@endforeach
						</select>
						@error('unit') <span class="text-danger">{{ $message }}</span> @enderror
					</div>
					<button type="submit" class="btn btn-primary">Simpan</button>
				</form>
			</div>
		</div>
	</div>
 </div>	
@stop

