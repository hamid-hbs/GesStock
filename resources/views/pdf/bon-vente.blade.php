<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body { font-family: sans-serif; font-size: 12px; }
h1 { font-size: 18px; } table { width: 100%; border-collapse: collapse; margin-top: 12px; }
th, td { border: 1px solid #999; padding: 6px; text-align: left; }
.total { font-weight: bold; }
</style></head><body>
<h1>Bon de vente {{ $vente->reference }}</h1>
<p>Date : {{ $vente->date_vente->format('d/m/Y') }}<br>
Client : {{ $vente->client->nom }} ({{ $vente->type_client }})<br>
@if($vente->membre)Apporteur : {{ $vente->membre->nom }}<br>@endif</p>
<table>
<tr><th>Article</th><th>Qté</th><th>PU</th><th>Sous-total</th></tr>
@foreach($vente->lignes as $l)
<tr><td>{{ $l->libelleArticle() }}</td><td>{{ $l->quantite }}</td><td>{{ number_format($l->prix_unitaire, 0, ',', ' ') }}</td><td>{{ number_format($l->sous_total, 0, ',', ' ') }}</td></tr>
@endforeach
</table>
<p class="total">Total : {{ number_format($vente->total, 0, ',', ' ') }} — Commission membre : {{ number_format($vente->commission_membre, 0, ',', ' ') }}</p>
</body></html>
