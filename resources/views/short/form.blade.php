<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Access</title>
    <style>
        body{font-family:Arial, sans-serif; display:flex; justify-content:center; padding:40px;}
        form{width:360px; display:flex; flex-direction:column; gap:14px;}
        .number-code div{display:flex; gap:8px; justify-content:space-between;}
        .code-input{width:48px; height:56px; font-size:24px; text-align:center;}
        .error{color:#b04b4b; font-size:14px;}
        input[type="submit"]{height:44px; font-size:16px; cursor:pointer;}
    </style>
</head>
<body>

<form method="POST" action="{{ route('short.verify', ['short' => $short]) }}" onsubmit="return enviar()">
    @csrf

    <h2>Security Code</h2>

    @if($errors->has('codigo'))
        <div class="error">{{ $errors->first('codigo') }}</div>
    @endif

    <fieldset class="number-code">
        <legend>Enter 6 digits</legend>
        <div>
            <input class="code-input" inputmode="numeric" maxlength="1" required>
            <input class="code-input" inputmode="numeric" maxlength="1" required>
            <input class="code-input" inputmode="numeric" maxlength="1" required>
            <input class="code-input" inputmode="numeric" maxlength="1" required>
            <input class="code-input" inputmode="numeric" maxlength="1" required>
            <input class="code-input" inputmode="numeric" maxlength="1" required>
        </div>
    </fieldset>

    <input type="hidden" name="codigo" value="">

    <input type="submit" value="Go">
</form>

<script>
    const inputElements = [...document.querySelectorAll('input.code-input')];

    // solo números + auto-advance
    inputElements.forEach((ele, index) => {
        ele.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value === '') {
                inputElements[Math.max(0, index - 1)].focus();
            }
        });

        ele.addEventListener('input', (e) => {
            const v = e.target.value.replace(/\D/g,'');
            const [first, ...rest] = v;
            e.target.value = first ?? '';

            const last = index === inputElements.length - 1;
            if (first !== undefined && !last) {
                inputElements[index + 1].focus();
                inputElements[index + 1].value = rest.join('');
                inputElements[index + 1].dispatchEvent(new Event('input'));
            }
        });
    });

    function enviar(){
        const code = inputElements.map(i => i.value).join('');
        document.querySelector('input[name="codigo"]').value = code;
        return true;
    }
</script>

</body>
</html>