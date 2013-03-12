<!DOCTYPE html>
<html>
  <head>
    <title>Авторизация - {$prj_name}.{$prj_zone}</title>
    <link href="/bundles/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="/bundles/admin/css/login.css" rel="stylesheet">
	<script src="/js/jquery.js"></script>
    <script src="/bundles/bootstrap/js/bootstrap.min.js"></script>
  </head>
  <body>
	{if $message}
	<div class="login-message">
		<div class="alert alert-{$message.type}">
			{$message.text}
		</div>
	</div>{/if}
	<div class="well login-form-container">
		<div class="login-title">
		<img src="{$theme_ref}/img/icons/icon_key.gif"> Авторизация
		</div>
		<div class="dotted-line"></div>
		<div class="login-form">
		<form class="form-horizontal" method="post">
		<div class="control-group">
			<label class="control-label" for="inputEmail">Логин</label>
			<div class="controls">
			<input type="text" id="inputEmail" name="_user">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="inputPassword">Пароль</label>
			<div class="controls">
			<input type="password" id="inputPassword" name="_password">
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
			<label class="checkbox">
				<input type="checkbox" name="_remember_me"> Запомнить меня
			</label>
			<button type="submit" class="btn">Войти</button>
			</div>
		</div>
		</form>
		<div class="dotted-line"></div>	
		<div><b>Забыли свой пароль?</b><br>
		Следуйте на <a href="/admin/forgot">форму для запроса пароля</a>.<br>
		<a href="/" target="_blank">Перейти к просмотру сайта</a>.
		</div>

	</div> 
	  
  </body>
</html>