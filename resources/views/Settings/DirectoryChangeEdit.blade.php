@extends("layout.main")

@section('content')

    <div class="content_center">


        <form method="post" class="row g-3" action="{{route('DirectoryChange.update',[$ModelName,$Name])}}">
            @csrf
            @method('Patch')


            <label  class="form-label">Изменить переменную</label>
            <input type="text" class="form-control" id="aircraftType" name="Name" value="{{$Name}}">


            <button type="submit" class="btn btn-primary">Cохранить</button>

        </form>




    </div>

@endsection
