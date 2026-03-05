@extends("layout.main")

@section('content')

    <div class="content_center">


        <form method="post" class="row g-3" action="{{route('DirectoryChange.store',[$ModelName])}}">
            @csrf
            @method('Post')



            <label  class="form-label">Добавить  переменную</label>
            <input type="text" class="form-control" id="aircraftType" name="Name">


            <button type="submit" class="btn btn-primary">Cохранить</button>

        </form>




    </div>

@endsection
