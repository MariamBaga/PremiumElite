@extends('adminlte::page')
@section('title','Carte réseau')

@section('content_header')
  <h1>Carte réseau (plaques / extensions / clients)</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body p-0">
    <div id="map" style="height: 70vh;"></div>
  </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
 integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@stop
@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
 integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const map = L.map('map').setView([12.6392, -8.0029], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  const res = await fetch('{{ route('map.data') }}');
  const geo = await res.json();

  // (Optionnel) debug rapide
  console.log('Features:', geo.features?.length ?? 0);

  const styleByLayer = f => {
    const layer = f.properties?.layer;
    if (layer === 'plaque') {
      return { color: '#0d6efd', weight: 2.5, fillOpacity: 0.12 };
    }
    if (layer === 'extension') {
  if (f.properties?.statut === 'planifie') return { color: '#ffc107', weight: 4, dashArray:'6 4' };
  if (f.properties?.statut === 'en_cours') return { color: '#0d6efd', weight: 4 };
  if (f.properties?.statut === 'termine')  return { color: '#198754', weight: 4 };
}

    if (layer === 'client') {
      return { radius: 5, weight: 1, fillOpacity: 0.8 };
    }
    return {};
  };

  const onEach = (feature, layer) => {
    const p = feature.properties || {};
    if (p.layer === 'client') {
      layer.bindPopup(`<b>Client</b><br>${p.name ?? ''}<br>Zone: ${p.zone ?? '-'}`);
    } else if (p.layer === 'plaque') {
      layer.bindPopup(`<b>Plaque ${p.code}</b><br>${p.nom ?? ''}<br>Statut: ${p.statut}`);
    } else if (p.layer === 'extension') {
      layer.bindPopup(`<b>Extension ${p.code}</b><br>Statut: ${p.statut}<br>Zone: ${p.zone ?? '-'}`);
    }
  };

  const layerGroup = L.geoJSON(geo, {
    pointToLayer: (f, latlng) => {
      if (f.properties?.layer === 'client') {
        return L.circleMarker(latlng, styleByLayer(f));
      }
      // pour LineString/Polygon Leaflet gère via "style"
      return undefined;
    },
    style: styleByLayer,
    onEachFeature: onEach
  }).addTo(map);

  // 👉 Zoome automatiquement sur les données si présentes
  try {
    const bounds = layerGroup.getBounds();
    if (bounds && bounds.isValid()) {
      map.fitBounds(bounds.pad(0.2));
    }
  } catch (e) {
    // ignore
  }
});
</script>


@stop
