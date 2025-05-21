<form id="formEditarUsuario">
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" class="form-control" value="{{ $user->name }}">
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="{{ $user->email }}">
    </div>
    <div class="form-group">
        <label for="">Acceso de Usuario </label>
        <select name="type_user" class="form-control">
            <option value="1" {{ $user->type_user == 1 ? 'selected' : '' }}>Administrador</option>
            <option value="2" {{ $user->type_user == 2 ? 'selected' : '' }}>Ofertas</option>
            <option value="3" {{ $user->type_user == 3 ? 'selected' : '' }}>Proyectos</option>
            <option value="4" {{ $user->type_user == 4 ? 'selected' : '' }}>Usuario</option>
        </select>
    </div>
    <!-- más campos según necesites -->
    <button type="submit" class="btn btn-success">Guardar</button>
</form>
