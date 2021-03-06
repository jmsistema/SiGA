<h2 class="principal">Novo Processo Seletivo para o curso <b><i><?php echo $course['course_name'];?></i></b> </h2>

<div id="selection_process_error_status"></div>

<?php

	$name = array(
		"name" => "selective_process_name",
		"id" => "selective_process_name",
		"type" => "text",
		"class" => "form-campo form-control",
		"placeholder" => "Informe o nome do edital",
		"maxlength" => "60"
	);

	$vacancies = array(
		"id" => "total_vacancies",
		"name" => "total_vacancies",
		"type" => "number",
		"min" => 0,
		"steps" => 1,
		"class" => "form-control",
		"placeholder" => "Informe o número de vagas"
	);

	$passingScore = array(
		"id" => "passing_score",
		"name" => "passing_score",
		"type" => "number",
		"min" => 0,
		"max" => 100,
		"steps" => 1,
		"class" => "form-control",
		"placeholder" => "Nota de corte para classificação"
	);

	$phaseWeight = array(
		"type" => "number",
		"min" => 0,
		"max" => 5,
		"steps" => 1,
		"class" => "form-control",
		"placeholder" => "Informe o peso dessa fase"
	);

	$phaseGrade = array(
		"type" => "number",
		"min" => 0,
		"max" => 100,
		"steps" => 1,
		"class" => "form-control",
		"placeholder" => "Informe a nota de corte"
	);

	$saveProcessBtn = array(
		"id" => "open_selective_process_btn",
		"class" => "btn bg-primary pull-right",
		"content" => "Salvar e Continuar"
	);

	$hidden = array(
		"id" => "course",
		"name" => "course",
		"type" => "hidden",
		"value" => $course['id_course']
	);

	$canNotEdit = FALSE;
	$selectedStudentType = "";

	include '_form.php';

	$knockoutPhaseType = array(
		TRUE => 'Eliminatória',
		FALSE => 'Classificatória'
	);

?>
<!-- Selection Process Settings -->
<h3><i class="fa fa-cogs"></i> Fases do edital</h3>

<div class="row">
	<div class="col-md-8">
		<?= form_label("Selecione as fases que comporão o processo seletivo:"); ?>

		<h4><small><b>
		Marque as fases desejadas como "Sim".<br>
		Os pesos definidos são os pesos padrão.<br>
		Fique a vontade para alterar, lembrando que o peso máximo permitido é 5.<br>
		E a nota de corte máxima permitida é 100.
		</b></small></h4>

	<?php
		if(!empty($phases)){

			foreach($phases as $phase){

				// Homologation phase is obrigatory and do not have weight
				if($phase->getPhaseName() !== SelectionProcessConstants::HOMOLOGATION_PHASE){

				$selectName = "phase_select_".$phase->getPhaseId();
				$selectId = $selectName;
				$selectedItem = TRUE;

				$processPhases = array(
					TRUE => "Sim",
					FALSE => "Não",
				);

				$phaseWeight["id"] = "phase_weight_".$phase->getPhaseId();
				$phaseWeight["name"] = "phase_weight_".$phase->getPhaseId();
				$phaseWeight["value"] = $phase->getWeight();

				$gradeName = "phase_grade_".$phase->getPhaseId();
				$phaseGrade["id"] = $gradeName;
				$phaseGrade["name"] = $gradeName;
				$gradeDiv = $gradeName."_div";

				$phaseName = $phase->getPhaseName();

				$selectPhaseName = "phase_type_".$phase->getPhaseId();
				$selectPhaseId = $selectPhaseName;

				$fields = "phase_".$phase->getPhaseId()."_fields";
	?>	
				<hr>
				<div class="row">
					<div class="col-md-10">
						<div class="input-group">
							<span class="input-group-addon">
								<?= form_label($phaseName, $selectName); ?>
							</span>
							<div class="input-group-btn">
								<?= form_dropdown($selectName, $processPhases, $selectedItem, "id='{$selectId}', class='form-control'"); ?>
							</div>
						</div>
						<div id=<?=$fields?> style="display:none;">
							<div class="input-group">
								<span class="input-group-addon">
									<?= form_label("Tipo da fase", "phase_type"); ?>
								</span>
								<div class="input-group-btn">
									<?= form_dropdown($selectPhaseName, $knockoutPhaseType, $selectedItem, "id='{$selectPhaseId}', class='form-control'"); ?>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
										<?= form_label("Peso", $phaseWeight['name']); ?>
									<?= form_input($phaseWeight); ?>
								</div>
								<div class="col-md-6" id=<?=$gradeDiv?> style="display: none;">
										<?= form_label("Nota de Corte da Fase", $phaseGrade['name']); ?>
									<?= form_input($phaseGrade); ?>
								</div>
							</div>
						</div>
					</div>
				</div>

			<?php   }else{ ?>

					<div class="row">
						<div class="col-md-10">
							<div class="input-group">
							<span class="input-group-addon">

								<?= form_label($phase->getPhaseName()); ?>
							<span class="label label-default">Fase obrigatória e sem peso.</span>
							</span>

							</div>
						</div>
					</div>

	<?php  			}
		    }
		}else{
			callout("info", "Não há fases cadastradas. Não é possível abrir o edital.");
			$submitBtn['disabled'] = TRUE;
		}
	?>

		</div>
	</div>

	<br>
	<br>

	<h4><i class="fa fa-sort-amount-asc"></i> Ordem das fases do edital</h4>

	<br>
	Defina a ordem de execução das fases para este edital arrastando as fases para a posição desejada:
	<br>

	<div id="phases_list_to_order"></div>

	<br>

	<div class="col-sm-2 pull-left">
		<?= anchor(
			"program/selectiveprocess/courseSelectiveProcesses/{$course['id_program']}",
			"Voltar",
			"class='btn btn-danger'"
		); ?>
	</div>
	<div class="col-sm-2 pull-right">
		<?= form_button($saveProcessBtn); ?>
	</div>

	<br><br><br>
	

