<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

@php
    function renderElement() {
        // 
    }
    foreach ($payload['elements'] as $elem) {
        $toRender = "<".$elem['tag']." ";
        foreach ($elem['props'] as $evt => $prop) {
            $toRender .= $evt."='".$prop."'";
        }
        $toRender .= ">".@$elem['children']."</".$elem['tag'].">";
        echo $toRender;
    }
@endphp
    
<script>
@php
header('Content-Type: application/javascript');
foreach($payload['states'] as $key => $value) {
    echo "let $key = ".json_encode($value).";\n";
}
foreach ($payload['functions'] as $name => $content) {
    echo "function " . $name . "() {".$content."}";
}
@endphp
</script>

</body>
</html>