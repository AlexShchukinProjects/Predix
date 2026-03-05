@extends("layout.main")

@section('content')

    <div class="content_center">




        <div class="">



            <table class="table table-hover">
                <thead>

                <tr class="table-color_blu">
                    <th scope="col">#</th>
                    <th scope="col">Название</th>

                    <th scope="col"></th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>







                @foreach($variables as $variable )

                    <tr>

                        <th scope="row">{{$variable->id}}</th>
                        <td>{{$variable->Name}}</td>




                        <td>

                            <form action="{{route('DirectoryChange.edit', [$ModelName,$variable->Name])}}" method="get">
                                @csrf
                                @method('GET')
                                <button class="btn" ><svg style="color:gray" class="" width="20" height="20">   <use xlink:href="#pencil"></use></svg></button>
                            </form>

                        </td>

                        <td>
                            <form action="{{route('fleet.delete', $variable->id)}}" method="post">
                                @csrf
                                @method('Delete')

                                <button class="btn"><svg style="color:gray" class="" width="20" height="20">   <use xlink:href="#trash"></use></svg> </button>


                            </form>

                        </td>
                    </tr>

                </tbody>



                @endforeach

            </table>


        </div>
        <div>

            <input type="button" class="btn btn-warning" style="margin-bottom: 15px" value="Добавить1" onclick="window.location='/CreateDirectory/{{$ModelName}}'">


        </div>

    </div>

@endsection
