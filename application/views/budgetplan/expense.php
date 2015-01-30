<h2 class="principal">Adicionar uma despesa</h2>

<div class="form-box-logged" id="login-box">
	<div class="header" style="background-color: #3c8dbc">Adicionar despesa</div>

	<div class="body bg-gray">
		<?= form_open("expense/save") ?>
		<?= form_hidden("budgetplan_id", $budgetplan['id']) ?>
		<?= form_hidden("continue", "ok") ?>

		<div class="form-group">
			<?= form_label("Saldo disponível", "balance") ?>
			<?= form_input(array(
				"name" => "balance",
				"id" => "balance",
				"type" => "number",
				"class" => "form-campo",
				"value" => $budgetplan['balance'],
				"readonly" => "readonly"
			)) ?>
		</div>

		<div class="form-group">
			<?= form_label("Valor", "value") ?>
			<?= form_input(array(
				"name" => "value",
				"id" => "value",
				"type" => "number",
				"class" => "form-campo",
				"required" => "required"
			)) ?>
		</div>

		<div class="form-group">
			<?= form_label("Natureza da despesa", "nature") ?>
			<?= form_dropdown('type', $types) ?>
		</div>

		<div class="form-group">
			<?= form_label("Ano", "year") ?>
			<?= form_input(array(
				"name" => "year",
				"id" => "year",
				"type" => "number",
				"class" => "form-campo",
				"value" => "2015"
			)) ?>
		</div>

		<div class="form-group">
			<?= form_label("Mês da liberação", "month") ?><br>
			<?= form_dropdown('month', $months) ?>
		</div>

		<div class="footer">
			<?= form_button(array(
				"class" => "btn bg-light-blue btn-block",
				"type" => "sumbit",
				"content" => "Salvar",
				"onclick" => "confirmation()"
			)) ?>
		</div>

		<?= form_close() ?>

		<div class="footer">
			<a href="<?=base_url("planoorcamentario/{$budgetplan['id']}")?>" class='btn bg-light-blue btn-block'>Voltar</a>
		</div>
	</div>
</div>

<script>
	function confirmation() {
		var value = parseInt(document.getElementById("value").value);
		var balance = parseInt(document.getElementById("balance").value);

		if (value >= balance) {
			if (!confirm("Atenção! Este plano não tem saldo insuficiente para esta despesa. Deseja continuar?")) {
				document.getElementsByName("continue")[0].value = "";
			}
		}
	}
</script>