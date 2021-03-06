<h3>Return Gear</h3>
<form action='../save_return' method="post">
	<div class="form-group">
		<input type="submit" class="btn btn-info" data-bind="enable: selectedGear().length>0" value="Return Selected Gear">
	</div>

	<h4>Gear still to be returned</h4>
	<table id='dataTable1' class='table'><tfoot>
		<tr>
			<?php 
				foreach ($data['fields'] as $index => $field){
					echo('<th>');					
					echo('</th>');
				}
			?>
		</tr>
	</tfoot></table>

	<input type="hidden" name="selectedGear" data-bind="value: ko.toJSON(selectedGear)" />
	<div data-bind="visible: returnedGear().length>0">
		<h4>Gear already returned</h4>
		<table id='dataTable2' class='table'><tfoot>
			<tr>
				<?php 
					foreach ($data['fields'] as $index => $field){
						echo('<th>');					
						echo('</th>');
					}
				?>
			</tr>
		</tfoot></table>
	</div>

	<div class="form-group">
		<input type="submit" class="btn btn-info" data-bind="enable: selectedGear().length>0" value="Return Selected Gear">
	</div>
</form>
<script>
	// This script calls the javascript which sets up that dataTable and stuff
  $(document).ready(function() {
    model = new ViewModel(<?php echo json_encode($data) ?>);
    ko.applyBindings(model);
    model.init();
  })
</script>

<style type="text/css">
	tr.row_selected td{background-color:#ccccff !important;}
</style>