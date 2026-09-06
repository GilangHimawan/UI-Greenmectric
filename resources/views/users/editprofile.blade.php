@extends('adminlte::page')

@section('title', 'Profil User')

@section('content_header')
    <h1 class="m-0 text-dark"></h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
				<h1>Profil</h1>

				<div class="mt-2">
					@include('layouts.partials.messages')
				</div>
				
				<div class="container mt-4">
					<form method="post" action="{{ route('users.updateprofile', $user->id) }}" enctype="multipart/form-data">
						@method('patch')
						@csrf
						<div class="mb-3">
							<label for="name" class="form-label">Name</label>
							<input value="{{ $user->name }}" 
								type="text" 
								class="form-control" 
								name="name" 
								placeholder="Name" required>

							@if ($errors->has('name'))
								<span class="text-danger text-left">{{ $errors->first('name') }}</span>
							@endif
						</div>
						<div class="mb-3">
							<label for="email" class="form-label">Email</label>
							<input value="{{ $user->email }}"
								type="email" 
								class="form-control" 
								name="email" 
								placeholder="Email address" required>
							@if ($errors->has('email'))
								<span class="text-danger text-left">{{ $errors->first('email') }}</span>
							@endif
						</div>
						<div class="mb-3">
							<label FOR="Password" class="form-label">Password</label>
							<input value="{{ $user->password }}"
								type="password" 
								class="form-control" 
								name="password" 
								placeholder="Isi jika ingin merubah password" >
							@if ($errors->has('password'))
								<span class="text-danger text-left">{{ $errors->first('password') }}</span>
							@endif
						</div>
						<div class="mb-3">
							<label for="avatar" class="form-label">Avatar</label>
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="customFile" name="avatar">
								<label class="custom-file-label" for="customFile">Choose file</label>
							</div>
							<br><br>
							@if($user->avatar)
								<img src="{{ asset('storage/avatars/'.$user->avatar) }}" alt="Avatar" class="img-thumbnail" width="150">
							@endif
							@if ($errors->has('avatar'))
								<span class="text-danger text-left">{{ $errors->first('avatar') }}</span>
							@endif
						</div>

						{{-- 
						<div class="mb-3">
							<label for="password" class="form-label">Ubah Password</label>
							<input value="{{ $user->bidang_ilmu }}"
								type="password" 
								class="form-control" 
								name="password" 
								placeholder="Isi jika ingin merubah password" >
							@if ($errors->has('bidang_ilmu'))
								<span class="text-danger text-left">{{ $errors->first('bidang_ilmu') }}</span>
							@endif
						</div>
						 --}}
						<input value="{{ $user->username }}" type="hidden" name="username">


						<button type="submit" class="btn btn-primary">Simpan Profil</button>
						<a href="{{ route('home.index') }}" class="btn btn-default">Batal</a>
					</form>
				</div>
		
			</div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
	// Add the following code if you want the name of the file appear on select
	$(".custom-file-input").on("change", function() {
	var fileName = $(this).val().split("\\").pop();
	$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
	});
</script>
@endsection