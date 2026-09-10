<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body { font-family: sans-serif; font-size: 12px; }
h1 { font-size: 18px; } table { width: 100%; border-collapse: collapse; margin-top: 12px; }
th, td { border: 1px solid #999; padding: 6px; text-align: left; }
</style></head><body>
<h1>Bon de production {{ $production->reference }}</h1>
<p>Date : {{ $production->date_production->format('d/m/Y') }}<br>
Notes : {{ $production->notes ?? '—' }}</p>
<table>
<tr><th>Article</th><th>Quantité produite</th></tr>
@foreach($production->lignes as $l)
<tr><td>{{ $l->libelleArticle() }}</td><td>{{ $l->quantite }}</td></tr>
@endforeach
</table>
</body></html>
