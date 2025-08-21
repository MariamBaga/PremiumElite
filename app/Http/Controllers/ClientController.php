<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Pas de __construct middleware ici ; on protège via routes

    public function index(Request $request)
    {
        $q = Client::query()
            ->when($request->filled('type'), fn($qry)=>$qry->where('type',$request->type))
            ->when($request->filled('search'), function($qry) use ($request) {
                $s = '%'.$request->search.'%';
                $qry->where(function($sub) use ($s){
                    $sub->where('nom','like',$s)
                        ->orWhere('prenom','like',$s)
                        ->orWhere('raison_sociale','like',$s)
                        ->orWhere('telephone','like',$s)
                        ->orWhere('email','like',$s)
                        ->orWhere('adresse_ligne1','like',$s);
                });
            })
            ->latest();

        return view('clients.index', [
            'clients' => $q->paginate(15)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(StoreClientRequest $request)
    {
        $client = Client::create($request->validated());
        return redirect()->route('clients.show', $client)->with('success','Client créé avec succès.');
    }

    public function show(Client $client)
    {
        $client->loadCount('dossiers');
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());
        return redirect()->route('clients.show', $client)->with('success','Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success','Client supprimé.');
    }
}
