<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }    

    /*
|-------------------------------------------------------------------------------
| Relazione Azienda
|-------------------------------------------------------------------------------
| Description:    Restituisce l'azienda legata all'user
| Parameters:     id_azn_anagrafica
| Schema: aziende.azn_anagrafiche
| Db Table: lotti
| Type:     hasOne
*/

public function azienda() {
    return $this->hasOne('App\aziende\azn_anagrafiche', 'id_azn_anagrafica', 'id_azn_anagrafica');
}

/*
|-------------------------------------------------------------------------------
| Relazione Punto Vendita
|-------------------------------------------------------------------------------
| Description:    Restituisce il punto vendita legato all'user
| Parameters:     id_azn_puntovendita
| Schema: aziende.azn_puntivendita
| Db Table: lotti
| Type:     hasOne
*/

public function puntov() {
return $this->hasOne('App\aziende\azn_puntivendita', 'id_azn_puntovendita', 'id_azn_puntovendita');
}
}