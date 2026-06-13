<h2>Orte</h2>
<table>
  <tr><th>ID</th><th>Code</th><th>Name</th><th>Bereich</th><th>Parent</th><th>Notizen</th><th>Artikel</th><th>Aktionen</th></tr>
  <?php foreach($locations as $l): $cnt=db()->prepare('SELECT COUNT(*) FROM items WHERE location_id=?'); $cnt->execute([$l['id']]); ?>
    <tr>
      <td><?=h($l['id'])?></td>
      <td><?=h($l['code'])?></td>
      <td><?=h(($l['parent_id']?'↳ ':'').$l['name'])?></td>
      <td><?=h($l['area'])?></td>
      <td><?=h($l['parent_code'])?></td>
      <td><?=h($l['notes'])?></td>
      <td><?=h($cnt->fetchColumn())?></td>
      <td>
        <button type="button" class="button ghost" data-fill-location='<?=h(json_encode($l, JSON_UNESCAPED_UNICODE))?>'>Bearbeiten</button>
        <form method="post" action="?p=delete_location" class="inline" onsubmit="return confirm('Ort wirklich löschen? Das geht nur ohne Unterorte, Behälter und Artikel.')">
          <?=csrf_field()?>
          <input type="hidden" name="id" value="<?=h($l['id'])?>">
          <button class="danger">Löschen</button>
        </form>
      </td>
    </tr>
  <?php endforeach?>
</table>
<h3>Ort anlegen/bearbeiten</h3>
<form method="post" action="?p=save_location" class="form small" id="location-form">
  <?=csrf_field()?>
  <input name="id" placeholder="ID für Update optional">
  <input required name="code" placeholder="Code">
  <input required name="name" placeholder="Name">
  <input name="area" placeholder="Bereich">
  <select name="parent_id"><option value="">Kein Parent</option><?php foreach($locations as $l):?><option value="<?=h($l['id'])?>"><?=h($l['code'].' '.$l['name'])?></option><?php endforeach?></select>
  <textarea name="notes" placeholder="Notizen"></textarea>
  <button>Speichern</button>
</form>
