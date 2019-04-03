    {include file="string:{config name=emailheaderhtml}"}
<br/><br/>
<p>
    {if $salutation == 'mrs'}
    Dear Mrs {$lastname},
    {elseif $salutation == 'mr'}
    Dear Mr {$lastname},
    {else}
    Dear {$firstname} {$lastname},
    {/if}<br/>
    <br/>
    Please click on the following link to confirm your email adress:<br/>
    <br/>
<p>{$sConfirmLink}</p>
<br/>
You can change your e-mail address again at any time in your customer account.<br/>
<br/>
Best regards<br/>
<br/>
ElringKlinger Kunststofftechnik GmbH


html

{include file="string:{config name=emailheaderhtml}"}
<br/><br/>
<p>
    {if $salutation == 'mrs'}
        Dear Mrs {$lastname},
    {elseif $salutation == 'mr'}
        Dear Mr {$lastname},
    {else}
        Dear {$firstname} {$lastname},
    {/if}<br/>
    <br/>
        Please click on the following link to confirm your email adress:<br/>
    <br/>
<p>{$sConfirmLink}</p>
<br/>
You can change your e-mail address again at any time in your customer account.<br/>
<br/>
Best regards<br/>
<br/>
ElringKlinger Kunststofftechnik GmbH
