<?php require 'include/prehtml.php'; ?>
<?php require_active_firm(); ?>
<?php require 'include/stock_control.php'; ?>
<?php
	$sql = "SELECT firms.name, firms.networth, firms.cash, firms_positions.pay_flat, firms_positions.bonus_percent, firms_positions.next_pay_flat, firms_positions.next_bonus_percent FROM firms LEFT JOIN firms_positions ON firms.id = firms_positions.fid WHERE firms.id = $eos_firm_id AND firms_positions.pid = $eos_player_id";
	$paydata = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
	if(empty($paydata)){
		fbox_echoout('Unable to confirm your position in the company. Please make sure you are still an employee here.');
	}else{
		$firm_name = $paydata["name"];
		$firm_cash = $paydata["cash"];
		$firm_employee_pay_flat = $paydata["next_pay_flat"];
		$firm_employee_bonus_percent = $paydata["next_bonus_percent"];
	}
	$sql = "SELECT SUM(bonus_percent) FROM firms_positions WHERE fid = $eos_firm_id";
	$bonus_percent_spent = $db->query($sql)->fetchColumn();

	$min_salary = $paydata["pay_flat"];
	$max_salary = $paydata["pay_flat"] + max(10000000, floor(0.2 * $paydata["pay_flat"]));
	$min_bonus = 0;
	$max_bonus = max(100 * $paydata["bonus_percent"], min(2000, 8000 - 100 * $bonus_percent_spent));
?>
		<script type="text/javascript">
			var salary, salary_temp;
			var bonus, bonus_temp;
			var salary_max = <?= $max_salary ?>;
			var salary_min = <?= $min_salary ?>;
			var bonus_max = <?= $max_bonus ?>;
			var bonus_min = <?= $min_bonus ?>;

			function salaryMax(){
				salary = salary_max;
				document.getElementById('salary').value = salary;
				checkSalary();
			}
			function salaryMin(){
				salary = salary_min;
				document.getElementById('salary').value = salary;
				checkSalary();
			}
			function checkSalary(){
				salary = Math.floor(stripCommas(document.getElementById('salary').value));
				document.getElementById('salary').value = salary;
				document.getElementById('salary_visible').value = salary/100;
				jQuery("#slider_target").slider("value", salary);
			}
			function updateSalary(){
				salary_temp = document.getElementById('salary_visible').value;
				if(salary_temp.charAt(salary_temp.length-1) == ".") {
					return false;
				}
				salary = Math.round(stripCommas(salary_temp)*100);
				if(salary != '' && !isNaN(salary)){
					if(salary > salary_max){
						salary = salary_max;
						document.getElementById('salary_visible').value = salary/100;
					}
					if(salary < salary_min){
						salary = salary_min;
						document.getElementById('salary_visible').value = salary/100;
					}
					document.getElementById('salary').value = salary;
					checkSalary();
				}
			}
			var checkSalaryTimeout;
			function initUpdateSalary(skipTimeout){
				clearTimeout(checkSalaryTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateSalary();
				}else{
					checkSalaryTimeout = setTimeout("updateSalary();", 1000);
				}
			}
			function bonusMax(){
				bonus = bonus_max;
				document.getElementById('bonus').value = bonus;
				checkBonus();
			}
			function bonusMin(){
				bonus = bonus_min;
				document.getElementById('bonus').value = bonus;
				checkBonus();
			}
			function checkBonus(){
				bonus = Math.floor(stripCommas(document.getElementById('bonus').value));
				document.getElementById('bonus').value = bonus;
				document.getElementById('bonus_visible').value = bonus/100;
				jQuery("#slider_target_2").slider("value", bonus);
			}
			function updateBonus(){
				bonus_temp = document.getElementById('bonus_visible').value;
				if(bonus_temp.charAt(bonus_temp.length-1) == ".") {
					return false;
				}
				bonus = Math.round(stripCommas(bonus_temp)*100);
				if(bonus != '' && !isNaN(bonus)){
					if(bonus > bonus_max){
						bonus = bonus_max;
						document.getElementById('bonus_visible').value = bonus/100;
					}
					if(bonus < bonus_min){
						bonus = bonus_min;
						document.getElementById('bonus_visible').value = bonus/100;
					}
					document.getElementById('bonus').value = bonus;
					checkBonus();
				}
			}
			var checkBonusTimeout;
			function initUpdateBonus(skipTimeout){
				clearTimeout(checkBonusTimeout);
				if(typeof(skipTimeout) !== "undefined" && skipTimeout){
					updateBonus();
				}else{
					checkBonusTimeout = setTimeout("updateBonus();", 1000);
				}
			}
		</script>
<?php require 'include/stats_fbox.php'; ?>
	<h3>Ask for a Raise</h3>
	<div id="exec_pay_form">
		<b>Asking for a raise will by default discontinue your employment at the end of this term.</b> If the company owner or chairman sees and approves your request before then, your employment will continue at the new approved rates for all future terms. While it is not possible to ask for a drop in salary, the bonus can be adjusted between 0% to 20% provided that the company has not distributed the maximum possible amount (80%) to its employees.<br /><br />
		<form id="slider_form_1" class="default_slider_form" onsubmit="firmController.requestARaise();return false;">
			<h3 style="vertical-align:middle;">Base Salary<br /><small>(Per server day)</small></h3>
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="salaryMin();" /></div>
				<div id="slider_target" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="salaryMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					<input id="salary_visible" type="text" style="border: 2px solid #997755;text-align:center;" value="<?= $firm_employee_pay_flat / 100 ?>" size="13" maxlength="13" onkeyup="initUpdateSalary();" onchange="updateSalary();" />
					<input id="salary" type="hidden" style="display:none;" value="<?= $firm_employee_pay_flat ?>" maxlength="17" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<h3 style="vertical-align:middle;">Bonus Percentage<br /><small>(Per server day, as % of net earnings before tax)</small></h3>
			<div style="line-height:48px;" class="vert_middle">
				<div style="float:left;width:80px;"><img src="images/slider_min.gif" style="cursor:pointer;" onClick="bonusMin();" /></div>
				<div id="slider_target_2" class="slider_target"></div>
				<div style="float:left;width:80px;"><img src="images/slider_max.gif" style="cursor:pointer;margin-left: 10px;" onClick="bonusMax();" /></div>
				<div style="float:left;margin-left:80px;width:180px;" class="vert_middle">
					<input id="bonus_visible" type="text" style="border: 2px solid #997755;text-align:center;" value="<?= $firm_employee_bonus_percent ?>" size="5" maxlength="5" onkeyup="initUpdateBonus();" onchange="updateBonus();" />
					<input id="bonus" type="hidden" style="display:none;" value="<?= 100 * $firm_employee_bonus_percent ?>" maxlength="17" />
				</div>
				<div class="clearer"></div>
			</div>
			<br />
			<img class="big_action_button" src="images/button-trade-big.gif" id="fund_start_button" title="Confirm" onClick="firmController.requestARaise();" />
		</form>
		<script type="text/javascript">
			jQuery("#slider_target").slider({
				value: <?= $firm_employee_pay_flat ?>,
				min: salary_min,
				max: salary_max,
				slide: function(event, ui){
					jQuery("#salary").val(ui.value);
					checkSalary();
				}
			});
			jQuery("#slider_target_2").slider({
				value: <?= 100 * $firm_employee_bonus_percent ?>,
				min: bonus_min,
				max: bonus_max,
				slide: function(event, ui){
					jQuery("#bonus").val(ui.value);
					checkBonus();
				}
			});
		</script>
	</div>
		<br /><br />
		<input type="button" class="bigger_input jqDialog-close-btn" value="Close" />
<?php require 'include/foot_fbox.php'; ?>