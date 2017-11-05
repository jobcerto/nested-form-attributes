# nested-form-attributes

Para instalar rode o comando
> composer require jobcerto/nested-form-attributes:dev-master

Após instalado precisa adicionar a trait `HasNestedFormAttributes` e um `array` de relações no model que vai utilizar este recurso:

### Exemplo:
```php

use Jobcerto\NestedFormAttributes\HasNestedFormAttributes;

class User extends Model {

use HasNestedFormAttributes;

public $nested = [
   'addresses',
    'phone',
    'country',
    'plan',
    'comments',
    'tags',
    'friends'
]; 

...

``` 

### Como utilizar
Para utilizar o pacakge é só chamar o metodo `handlerNestedAttributes` usando um Model vazio e/ou um model já existente no banco de dados.

*Exemplos*

```php
$user = new User($request->user);
$user->handlerNestedAttributes($request->user);
...
$user = User::find(1);
$user->handlerNestedAttributes($request->user);
...
$user = auth()->user();
$user->handlerNestedAttributes($request->user);

```
Com isso a trait vai se encarregar de salvar todos os relacionamentos informados no formulrio de cadastro.

Um exmeplo de campos:

```html
<form action="{{ route('users.store') }}" method="POST" role="form">
    {{ csrf_field()  }}
    <legend>Cadastro de usuários</legend>
  
  <!-- Campo bsico para salvar o model user. -->
  
    <div class="form-group">
        <label>Nome</label>
        <input type="text" class="form-control" name="user[name]" value="EDUARDO">
    </div>
  
  <!-- Modelo padrão para um insert do tipo morphMany -->
    <div class="form-group">
        <label>Comentário</label>
        <input type="text" class="form-control" name="user[comments][][name]" value="MEU COMENTARIO">
    </div>
  
  <!-- Modelo padrão para um insert do tipo belongsTo -->
    <div class="form-group">
        <label>País</label>
        <select name="user[country]" class="form-control">
            @foreach(App\Country::all() as $country)
            <option value="{{ $country->id }}">{{ $country->name }}</option>
            @endforeach
        </select>
    </div>

  <!-- Modelo padrão para um insert do tipo hasOne -->
    <div class="form-group">
        <label>Telefones</label>
        <select name="user[phone][name]" class="form-control">
            <option value="519933333">519933333</option>
            <option value="5133333333">5133333333</option>
            <option value="5199999999">5199999999</option>
        </select>
    </div>
  
  <!-- Modelo padrão para um insert do tipo belongsTo -->
    <div class="form-group">
        <label>Plano</label>
        <select name="user[plan]" class="form-control">
            @foreach(App\Plan::all() as $plan)
            <option selected value="{{ $plan->id }}">{{ $plan->name }}</option>
            @endforeach
        </select>
    </div>
  
  <!-- Modelo padrão para um insert do tipo belongsToMany -->
    <div class="form-group">
        <label>Endereço</label>
        <select name="user[addresses][]" class="form-control" multiple>
            @foreach(App\Address::all() as $address)
            <option selected value="{{ $address->id }}">{{ $address->name }}</option>
            @endforeach
        </select>

    </div>
  
  <!-- Modelo padrão para um insert do tipo morphToMany -->
    <div class="form-group">
        <label>Tags</label>
        <select name="user[tags][]" class="form-control" multiple>
            @foreach(App\Tag::all() as $tag)
            <option selected value="{{ $tag->id }}">{{ $tag->name }}</option>
            @endforeach
        </select>

    </div>
  
  <!-- Modelo padrão para um insert do tipo hasMany -->
  <div class="form-group">
        <label>Friends</label>
        <select name="user[friends][]" class="form-control" multiple>
            @foreach(App\Friend::all() as $friend)
            <option selected value="{{ $friend->id }}">{{ $friend->name }}</option>
            @endforeach
        </select>

    </div>

    <button type="submit" class="btn btn-primary">SALVAR DADOS</button>
</form>


```

Como podem perceber, os campos são precedidos de `user` que é o model que esta utilizando a trait. O resto são formulários básicos.


