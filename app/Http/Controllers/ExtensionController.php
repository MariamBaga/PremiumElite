<?php

namespace App\Http\Controllers;

use App\Models\Extension;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtensionController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'zone'   => 'nullable|string|max:100',
            'statut' => ['nullable', Rule::in(array_keys(Extension::statuts()))],
            'q'      => 'nullable|string|max:100',
        ]);

        $q = Extension::query()
            ->when(!empty($data['zone']), fn($qr) => $qr->where('zone','like','%'.$data['zone'].'%'))
            ->when(!empty($data['statut']), fn($qr) => $qr->where('statut',$data['statut']))
            ->when(!empty($data['q']), function($qr) use ($data) {
                $s = '%'.$data['q'].'%';
                $qr->where(function($w) use ($s){
                    $w->where('code','like',$s)
                      ->orWhere('zone','like',$s);
                });
            })
            ->latest();

        return view('extensions.index', [
            'extensions' => $q->paginate(15)->withQueryString(),
            'statuts'    => Extension::statuts(),
        ]);
    }

    public function create()
    {
        return view('extensions.create', [
            'statuts' => Extension::statuts(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'code'          => 'required|string|max:50|unique:extensions,code',
            'zone'          => 'nullable|string|max:100',
            'statut'        => ['required', Rule::in(array_keys(Extension::statuts()))],
            'foyers_cibles' => 'nullable|integer|min:0',
            'roi_estime'    => 'nullable|numeric|min:0',
            'geom'          => 'nullable', // GeoJSON (string ou array)
        ]);

        // Parser GeoJSON si fourni en texte
        if (!empty($payload['geom']) && is_string($payload['geom'])) {
            $json = json_decode($payload['geom'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload['geom'] = $json;
            } else {
                return back()->withInput()->withErrors(['geom'=>'Le GeoJSON n’est pas valide.']);
            }
        }

        $ext = Extension::create($payload);
        return redirect()->route('extensions.show', $ext)->with('success','Extension créée.');
    }

    public function show(Extension $extension)
    {
        return view('extensions.show', [
            'e'       => $extension,
            'statuts' => Extension::statuts(),
        ]);
    }

    public function edit(Extension $extension)
    {
        return view('extensions.edit', [
            'e'       => $extension,
            'statuts' => Extension::statuts(),
        ]);
    }

    public function update(Request $request, Extension $extension)
    {
        $payload = $request->validate([
            'code'          => 'required|string|max:50|unique:extensions,code,'.$extension->id,
            'zone'          => 'nullable|string|max:100',
            'statut'        => ['required', Rule::in(array_keys(Extension::statuts()))],
            'foyers_cibles' => 'nullable|integer|min:0',
            'roi_estime'    => 'nullable|numeric|min:0',
            'geom'          => 'nullable',
        ]);

        if (!empty($payload['geom']) && is_string($payload['geom'])) {
            $json = json_decode($payload['geom'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload['geom'] = $json;
            } else {
                return back()->withInput()->withErrors(['geom'=>'Le GeoJSON n’est pas valide.']);
            }
        }

        $extension->update($payload);
        return redirect()->route('extensions.show', $extension)->with('success','Extension mise à jour.');
    }

    public function destroy(Extension $extension)
    {
        $extension->delete();
        return redirect()->route('extensions.index')->with('success','Extension supprimée.');
    }
}
