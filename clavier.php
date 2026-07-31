<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-keyboard@latest/build/css/index.css">
</head>

<body>
<input class="input" id="keyboard"  placeholder="Tap on the virtual keyboard to start" />
<div class="simple-keyboard" style="display: none"></div>

<script src="lib/dist/js/jquery.js"></script>
<script src="lib/keyboard/build/index.js"></script>
<script type="text/javascript">

    $('#keyboard').click(function(){
        $('.simple-keyboard').show()
    });

    $(document).mouseup(function(e)
    {
        var container = $(".simple-keyboard");
        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0)
        {
            container.hide();
        }
    });
    const Keyboard = window.SimpleKeyboard.default;

    const myKeyboard = new Keyboard({
        onChange: input => onChange(input),
        onKeyPress: button => onKeyPress(button)
    });

    function onChange(input) {
        document.querySelector(".input").value = input;
        console.log("Input changed", input);
    }

    function onKeyPress(button) {
        console.log("Button pressed", button);
    }
</script>
</body>
</html>
