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

  const styleByLayer = f => {
    const layer = f.properties?.layer;
    if (layer === 'plaque')    return { color: '#0d6efd', weight: 2, fillOpacity: 0.1 };
    if (layer === 'extension') return { color: '#198754', weight: 2, dashArray:'4 4' };
    if (layer === 'client')    return { };
    return {};
  };

  const onEach = (feature, layer) => {
    const p = feature.properties || {};
    if (p.layer === 'client') {
      layer.bindPopup(`<b>Client</b><br>${p.name ?? ''}<br>Zone: ${p.zone ?? '-'}`);
    } else if (p.layer === 'plaque') {
      layer.bindPopup(`<b>Plaque ${p.code}</b><br>${p.nom ?? ''}<br>Statut: ${p.statut}`);
    } else if (p.layer === 'extension') {
      layer.bindPopup(`<b>Extension ${p.code}</b><br>Statut: ${p.statut}`);
    }
  };

  L.geoJSON(geo, {
    pointToLayer: (f, latlng) => {
      if (f.properties?.layer === 'client') {
        return L.circleMarker(latlng, { radius: 4, weight:1, fillOpacity:0.7 });
      }
      return L.marker(latlng);
    },
    style: styleByLayer,
    onEachFeature: onEach
  }).addTo(map);
});
</script>
@stop
