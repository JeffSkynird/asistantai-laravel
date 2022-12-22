<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
      
        * {
            font-family: Verdana, Geneva, sans-serif;
        }

        .title {
            display: flex;
            justify-content: center;
        }

        .personal_data>h3 {
            margin-top: 10px;
            margin-bottom: 10px;
            border-bottom-color: black;
            border-bottom-style: dashed;
            border-bottom-width: 2px;
            padding-bottom: 5px;
        }

        .personal_data {
            margin: 10px;
            background-color: whitesmoke;
            padding: 5px;
        }

        ul {
            margin: 0px;
            padding: 0px;
        }

        li {
            font-weight: bold;
            list-style: none;
            font-size: 14px;
            margin-bottom: 5px;
        }

        li>span {
            font-weight: normal;
        }

        #customers {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        #customers td,
        #customers th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #customers tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        #customers tr:hover {
            background-color: #ddd;
        }

        #customers th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #3f51b5;
            color: white;
        }

        .banner {
            text-align: left;
            margin-top:-60px;
            margin-left:-50px;
            width: 100%;
            object-fit: cover;
            text-align: center;
            display: block;
            margin-bottom: 20px;
        }

        .left {
            text-align: left;


        }

        .right {
            text-align: right;


        }

        .cabecera {
            display: flex;
            margin: 0px;
        }

        .subtitle {
            display: block;
        }

        .tg {


            margin: 0px auto;
        }
        .tg2 {


margin: 0px auto;
}
        .tg td {
            border-color: black;
            border-style: solid;
            border-width: 0px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            overflow: hidden;
            padding: 5px 5px;
            word-break: normal;
        }
        .tg2 td {
            border-color: black;
            border-style: solid;
            border-width: 1px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            overflow: hidden;
            padding: 5px 5px;
            word-break: normal;
        }

        .tg th {
            border-color: black;
            border-style: solid;
            border-width: 1px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            overflow: hidden;
            padding: 5px 5px;
            word-break: normal;
        }
        .tg2 th {
            border-color: black;
            border-style: solid;
            border-width: 1px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            overflow: hidden;
            padding: 5px 5px;
            word-break: normal;
        }

        .tg .tg-0lax {
            text-align: left;
            vertical-align: top
        }
        .footer {
               position:absolute;
               bottom:-50px;

            }
    </style>
</head>

<body>
    <div class="cabecera">
        <img src="{{ public_path('logopdf.png') }}" class="banner">
        <!--     <div class="right">
            <h3 style="margin-bottom:0px;margin-top:0px;">CENTRO NATURISTA F.CH</h3>
            <span class="subtitle">Chamba Morales Fausto Sebastian</span>
            <span class="subtitle">Matriz: Sucre s/n y Juan Montalvo</span>
            <span class="subtitle">Cell.: 0993040644</span>
        </div> -->
    </div>


    <header class="title" style="padding-bottom:0px;margin:0px;">
        <table class="tg" style=" width: 320px">

            <thead>
                <tr>
                    <th class="tg-0lax" style="color:#808080;font-weight:bold;border:none;">PACIENTE</th>
                    <th class="tg-0lax" style="border:none;">{{$pacient->names}} {{$pacient->last_names}}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="tg-0lax" style="color:#808080;font-weight:bold;">CÉDULA</td>
                    <td class="tg-0lax">{{$pacient->dni}}</td>
                </tr>
                <tr>
                    <td class="tg-0lax" style="color:#808080;font-weight:bold;">EDAD</td>
                    <td class="tg-0lax">{{$borndate}}</td>
                </tr>
                <tr>
                    <td class="tg-0lax" style="color:#808080;font-weight:bold;">FECHA</td>
                    <td class="tg-0lax">{{$order_date}}</td>
                </tr>
            </tbody>
        </table>

    </header>
    <p style="text-align:center;color:black;font-weight:bold;">INFORME DE LOS RESULTADOS</p>

    @foreach ($data as  $key => $dt)
    <section class="" style="margin-bottom:15px;">
    <p style="text-align:center;color:black;font-weight:bold;">{{$key}}</p>

        <table class="tg2" style="table-layout: fixed; width: 100%;">
            <thead>
                <tr>
                    <th class="tg-0pky"  style="background-color:#E5E5E5;">Examen</th>
                    <th class="tg-0pky" style="background-color:#E5E5E5;">Resultado</th>
                    <th class="tg-0lax" style="background-color:#E5E5E5;">Unidad</
                    <th class="tg-0lax" style="background-color:#E5E5E5;">Rango</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($dt as $d)
                <tr >
                    <td>{{ $d->exam }}</td>
                    <td>{{ $d->value }}</td>
                    <td>{{ $d->unity }}</td>
                    <td>{{ $d->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </section>
    @endforeach


    <img src="{{ public_path('footerFinal.png') }}" class="footer">

</body>

</html>