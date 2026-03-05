<form method="post" action={{route('planning.crew.store')}} >Добавление пилотов
@csrf
    <br>
    <div style="margin-top:20px">
    <label>Имя</label>
    <input type="text" name="firstNameCaptain"> <br>
    </div>

    <div style="margin-top:20px">
    <label>Фамилия</label>
    <input type="text" name="lastNameCaptain">

     </div>

    <button> Сохранить </button>

</form>

