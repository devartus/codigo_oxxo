<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*  __
 / _|_   _ || _  _ ||||_ _  _
( (_/o\|/ \| ]_|/o\|||/oY_|(c'
 \__\_/L_n|L|L| \_/L|L\(L| \_)
  */

//Request::setTrustedProxies(array('127.0.0.1'));

//Mostrar opciones de inicio
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', array());
})
->bind('inicio')
;

$app->match('/crea_codigo', function (Request $request) use ($app) {
    $urlImg = "";
    $form = $app['form.factory']->createBuilder('form')
        ->add('nombre','text')
        ->add('codigo','text')
        ->getForm();
        //echo $app['url_generator']->generate('imagen');

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();
        $urlImg = $app['url_generator']->generate('imagen')."/" .$data['codigo'];
        //echo $urlImg;
        //echo "<img src='".$urlImg."'>";

        //  return $app->redirect($urlImg);
    }

    // display the form
    return $app['twig']->render('carga.html', array(
        'form' => $form->createView(),
        'urlImg' => $urlImg
    ));
})
->bind("creaCodigo");

$app->get('/resultado', function (Request $request) use ($app){
    echo $request;
})
->bind("resultado");

$app->get('/imagen/{referencia}', function (Request $request,$referencia) use ($app){
    $myGet = array();
    $myGet['code'] = "BCGcode128";
    $myGet['filetype'] = "PNG";
    $myGet['dpi'] = 72;
    $myGet['scale'] = 2;
    $myGet['rotation'] = 0;
    $myGet['font_family'] = "Arial.ttf";
    $myGet['font_size'] = 14;
    //$myGet['text'] = "0003452345234523";
    $myGet['text'] = $referencia;

    // Check if the code is valid
    if (!file_exists('../src/config' . DIRECTORY_SEPARATOR . $myGet['code'] . '.php')) {
        //echo "<p>archivo no encontrado1</p>";
    }

    include_once('../src/config' . DIRECTORY_SEPARATOR . $myGet['code'] . '.php');

    $class_dir = '../src' . DIRECTORY_SEPARATOR . 'class';
    require_once($class_dir . DIRECTORY_SEPARATOR . 'BCGColor.php');
    require_once($class_dir . DIRECTORY_SEPARATOR . 'BCGBarcode.php');
    require_once($class_dir . DIRECTORY_SEPARATOR . 'BCGDrawing.php');
    require_once($class_dir . DIRECTORY_SEPARATOR . 'BCGFontFile.php');

    if (!include_once($class_dir . DIRECTORY_SEPARATOR . $classFile)) {
        //showError();
    }

    include_once('../src/config' . DIRECTORY_SEPARATOR . $baseClassFile);
    $imgE3 = "../var/codigos/cod3.png";
    $filetypes = array('PNG' => BCGDrawing::IMG_FORMAT_PNG, 'JPEG' => BCGDrawing::IMG_FORMAT_JPEG, 'GIF' => BCGDrawing::IMG_FORMAT_GIF);

    $codigoTemporal = getcwd() . "temporal.png";

    $drawException = null;
    //try {
        $color_black = new BCGColor(0, 0, 0);
        $color_white = new BCGColor(255, 255, 255);

        $code_generated = new $className();

        if (function_exists('baseCustomSetup')) {
            baseCustomSetup($code_generated, $myGet);
        }

        if (function_exists('customSetup')) {
            customSetup($code_generated, $myGet);
        }

        $code_generated->setscale(max(1, min(4, $myGet['scale'])));
        $code_generated->setBackgroundColor($color_white);
        $code_generated->setForegroundColor($color_black);

        if ($myGet['text'] !== '') {
            //echo "original texto ".$myGet['text'];
            $text = convertText($myGet['text']);
            $code_generated->parse($text);
        }
    //} catch(Exception $exception) {
      //  $drawException = $exception;
    //}

    $drawing = new BCGDrawing('', $color_white);
    if($drawException) {
        $drawing->drawException($drawException);
    } else {
        $drawing->setBarcode($code_generated);
        $drawing->setRotationAngle($myGet['rotation']);
        $drawing->setDPI($myGet['dpi'] === 'NULL' ? null : max(72, min(300, intval($myGet['dpi']))));
        $drawing->setFilename($imgE3);
        $drawing->draw();
        //echo $var;
    }

    //$imgE = $drawing->finish($filetypes[$myGet['filetype']]);


    //echo $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
    $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
    //$imgE = "../src/error.png";

    //$imgE2 = "../var/codigos/error2.png";
    $stream = function () use ($imgE3) {
        readfile($imgE3);
    };

    return $app->stream($stream, 200, array('Content-Type' => 'image/png'));
})
->value("referencia", "0")
->bind("imagen");


// $app->get('/login', function(Request $request) use ($app) {
//     return $app['twig']->render('login.twig', array(
//         'error'         => $app['security.last_error']($request),
//         'last_username' => $app['session']->get('_security.last_username'),
//     ));
// });

// $app->get('/admin', function () use ($app) {
//     return 'Admin site';
// });

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html',
        'errors/'.substr($code, 0, 2).'x.html',
        'errors/'.substr($code, 0, 1).'xx.html',
        'errors/default.html',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code, 'e'=>$e)), $code);
});

function convertText($text) {
    $text = stripslashes($text);
    if (function_exists('mb_convert_encoding')) {
        $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    return $text;
}
