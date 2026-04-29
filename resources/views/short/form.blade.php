<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Access</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            padding: 40px;
        }

        form {
            width: 360px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .number-code div {
            display: flex;
            gap: 8px;
            justify-content: space-between;
        }

        .code-input {
            width: 48px;
            height: 56px;
            font-size: 24px;
            text-align: center;
        }

        .error {
            color: #b04b4b;
            font-size: 14px;
        }

        input[type="submit"] {
            height: 44px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <form method="POST" action="{{ route('short.verify', ['short' => $short]) }}" onsubmit="return enviar()">
        @csrf

        <h2>Security Code</h2>

        @if ($errors->has('codigo'))
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
        const hiddenCodeInput = document.querySelector('input[name="codigo"]');

        function fillCodeInputs(value) {
            const digits = String(value || '')
                .replace(/\D/g, '')
                .slice(0, inputElements.length)
                .split('');

            inputElements.forEach((input, i) => {
                input.value = digits[i] ?? '';
            });

            const nextIndex = digits.length >= inputElements.length ?
                inputElements.length - 1 :
                digits.length;

            inputElements[nextIndex]?.focus();
        }

        inputElements.forEach((ele, index) => {
            ele.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (e.target.value === '' && index > 0) {
                        inputElements[index - 1].focus();
                    }
                    return;
                }

                // Bloquear teclas que no sean números o teclas de control
                const allowedKeys = [
                    'Tab', 'ArrowLeft', 'ArrowRight', 'Delete', 'Home', 'End'
                ];

                if (
                    !allowedKeys.includes(e.key) &&
                    !/^\d$/.test(e.key) &&
                    !e.ctrlKey &&
                    !e.metaKey
                ) {
                    e.preventDefault();
                }
            });

            ele.addEventListener('input', (e) => {
                const digits = e.target.value.replace(/\D/g, '');

                if (digits.length > 1) {
                    fillCodeInputs(digits);
                    return;
                }

                e.target.value = digits;

                if (digits && index < inputElements.length - 1) {
                    inputElements[index + 1].focus();
                }
            });

            ele.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                fillCodeInputs(pastedText);
            });
        });

        function enviar() {
            const code = inputElements.map(i => i.value).join('');
            hiddenCodeInput.value = code;
            return code.length === 6;
        }
    </script>

</body>

</html>
