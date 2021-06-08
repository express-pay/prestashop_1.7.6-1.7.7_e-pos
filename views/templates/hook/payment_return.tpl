{if $status == 'ok'}
	<h2>Ваш номер заказа: {$order_id}</h2>
	<table style="width: 100%;text-align: left;">
		<tbody>
				<tr>
					<td valign="top" style="text-align:left;">
					Вам необходимо произвести платеж в любой системе, позволяющей проводить оплату через ЕРИП (пункты банковского обслуживания, банкоматы, платежные терминалы, системы интернет-банкинга, клиент-банкинга и т.п.).
						<br />
						<br /> 1. Для этого в перечне услуг ЕРИП перейдите в раздел: <br />
						<b>Система "Расчет" (ЕРИП)-&gt;Сервис E-POS-&gt;E-POS - оплата товаров и услуг</b><br />
						<br /> В поле "Код" введите <b>{$order_id}</b> и нажмите "Продолжить"<br />
						<br /> 3.Проверить корректность информации<br />
						<br /> 4. Совершить платеж.<br />
					</td>
						<td style="text-align: center;padding: 40px 20px 0 0;vertical-align: middle">
							<img src="data:image/jpeg;base64,{$qr_code} "width="150" height="150"/></p>
							<p><b>{$qr_description}</b></p>
                            </td>
                    </tr>
            </tbody>
        </table>
{else}
	<p class="warning">
		{l s='We have noticed that there is a problem with your order. If you think this is an error, you can contact our' mod='expresspayepos'}
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='expresspayepos'}</a>
	</p>
{/if}