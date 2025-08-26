<?php

// app/Models/Extension.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Extension extends Model {
  protected $fillable = [
    'code','zone','statut','foyers_cibles','roi_estime','geom'
  ];
  protected $casts = ['geom'=>'array'];
}
