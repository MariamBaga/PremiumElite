<?php

// app/Models/Ticket.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'client_id','dossier_id','opened_by','assigned_team_id',
        'reference','type','priorite','statut','titre','description','date_resolution'
    ];
    protected $casts = ['date_resolution'=>'datetime'];

    protected static function booted(): void {
        static::creating(function($t){
            if (empty($t->reference)) {
                $seq = str_pad((string)((self::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
                $t->reference = 'TK-'.date('Y').'-'.$seq;
            }
        });
    }

    public function client(){ return $this->belongsTo(Client::class); }
    public function dossier(){ return $this->belongsTo(DossierRaccordement::class,'dossier_id'); }
    public function opener(){ return $this->belongsTo(User::class,'opened_by'); }
    public function team(){ return $this->belongsTo(Team::class,'assigned_team_id'); }
    public function comments(){ return $this->hasMany(TicketComment::class); }
}
