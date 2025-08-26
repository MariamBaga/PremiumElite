<?php

// app/Models/Plaque.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Plaque extends Model {
  protected $fillable = [
    'code','nom','zone','statut','foyers_raccordables','pbo_installes','coverage','geom'
  ];
  protected $casts = ['geom'=>'array'];
}
