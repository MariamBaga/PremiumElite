<?php



// database/seeders/TeamSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // if (Team::count() > 0) return;

        // $users = User::orderBy('id')->take(6)->get();
        // if ($users->count() < 2) return;

        // $t1 = Team::create(['name'=>'Équipe Alpha','zone'=>'Nord']);
        // $t2 = Team::create(['name'=>'Équipe Beta','zone'=>'Sud']);

        // $t1->members()->attach($users->pluck('id'));
        // $t2->members()->attach($users->pluck('id')->slice(0,3));

        // // définir un chef pour chaque
        // $t1->setLeader($users[0]);
        // $t2->setLeader($users[1]);
    }
}

