<h4>
<a href="{raURL node=cart}">Уточнение заказа</a> &rarr;
{if !$user.id}<a href="{raURL node=cart method=account}">Авторизация</a> &rarr;{/if}
Параметры заказа &rarr;
<span>Подтверждение заказа</span>
</h4>
<br>
<form class="form-horizontal" name="mainForm" method="post">
<!-- Блок способ оплаты -->
  <div class="control-group">
    <label class="control-label">Получение товара</label>
    <div class="controls">
		{foreach from=$deliveryTypes item=deliveryType name=delivery}
		<input onclick="setDeliveryType({$deliveryType.id})" value="{$deliveryType.id}" name="deliveryType"{if $deliveryType.id == $smarty.session.deliveryType} checked="checked"{/if} id="deliveryType{$deliveryType.id}" type="radio">&nbsp;{$deliveryType.name} &nbsp;
		{/foreach}
		{foreach from=$deliveryTypes key=iKey item=deliveryType name="delivery"}
		<div class="delivery-text cut{if $deliveryType.id != $smarty.session.deliveryType} closed{/if}" id="deliveryDescr{$deliveryType.id}">{$deliveryType.description}
			{if $deliveryType.id == 5}
			<div>
				<select name="deliveryPoint" class="">
				{foreach from=$deliveryPoints item=deliveryPoint}
				<option value="{$deliveryPoint.id}"{if $deliveryPoint.id == $smarty.session.deliveryPoint} selected="selected"{/if}>{$deliveryPoint.name} ({$deliveryPoint.address})</option>
				{/foreach}
				</select>	
			</div>
			{/if}
		</div>
		{/foreach}
    </div>
  </div>
  <div class="control-group">
    <label class="control-label">Способ оплаты</label>
    <div class="controls">
		{foreach from=$payTypes item=payType name=pay}
		<input onclick="setPayType({$payType.id})" value="{$payType.id}" name="payType"{if $payType.id == $smarty.session.payType} checked="true"{/if} id="payType{$payType.id}" type="radio"> {$payType.name}
		{/foreach}
		<p class="cut closed" id="payDescr2">Для жителей городов России временно доступен только один вид оплаты: квитанция банка.</p>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label">Адрес доставки</label>
    <div class="controls">
		<dl class="dl-horizontal">
			<dt class="required">Адрес доставки</dt>
			<dd><textarea class="required2" rows="4" name="deliveryAddress" maxlength="500">{$smarty.session.deliveryAddress}</textarea></dd>
		</dl>
		<dl class="dl-horizontal">
			<dt class="required">ФИО получателя</dt>
			<dd><input class="required2" name="deliveryPerson" maxlength="60" value="{$smarty.session.deliveryPerson}" type="text"></dd>
		</dl>
		<dl class="dl-horizontal">
			<dt class="required">Электронная почта</dt>
			<dd>
				<input class="required2" name="deliveryEmail" maxlength="60" value="{$smarty.session.deliveryEmail}" type="text">
			</dd>
		</dl>
		<p class="cut">Укажите номера телефонов, по которым с Вами можно связаться для согласования заказа</p>	
		<dl class="dl-horizontal">
			<dt class="required">Мобильный телефон</dt>
			<dd>
				<input maxlength="30" class="required2" name="deliveryPhone" value="{$smarty.session.deliveryPhone}" type="text">
				<p class="comment">Формат: +7 (XXX) XXX-XX-XX</p>
			</dd>
		</dl>
		<dl class="dl-horizontal">
			<dt>Дополнительный номер<br> телефона
		   <p class="comment">(с кодом города)</p></dt>
			<dd>
				<input maxlength="30" name="deliveryPhoneAdd" value="{$smarty.session.deliveryPhoneAdd}" type="text">
			</dd>
		</dl>
    </div>
  </div>
  <div class="control-group">
    <div class="controls pull-right">
      <input type="submit" class="btn btn-large btn-warning" disabled="true" id="submitBtn" value="Продолжить" />
    </div>
  </div>			
</form>
<script type="text/javascript">
	bindDetailForm();
</script>
