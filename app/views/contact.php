<?if (isset($feedback)):?>
<div class="feedback <?=$feedback->type?>">
	<span></span>
	<p><?=$feedback->text?></p>
</div>
<?endif?>

<form id="contactForm" action="<?=url('Contact')?>" method="post">
	<fieldset>
		<legend><?=$legend?></legend>

		<?foreach ($fields as $field):?>
		<div class="<?=implode(' ', $field->class)?>">
			<?if ($field->tag == 'input'):?>
			<input type="text" name="<?=$field->name?>" id="<?=$field->id?>" value="<?=$field->value?>" class="<?=implode(' ', $field->class)?>" title="<?=$field->label?>" />

			<?elseif ($field->tag == 'textarea'):?>
			<textarea name="<?=$field->name?>" id="<?=$field->id?>" class="<?=implode(' ', $field->class)?>" title="<?=$field->label?>" cols="20" rows="5"><?=$field->value?></textarea>
			<?endif?>

			<?if (isset($field->error)):?>
			<p class="error"><?=$field->error?></p>
			<?endif?>
		</div>
		<?endforeach?>

		<div class="buttons">
			<button type="submit" name="submit"><?=$labels->send_button?></button>
			<button type="submit" name="reset"><?=$labels->reset_button?></button>
		</div>

	</fieldset>
</form>