<?php
	require "yt/Translator.php";
	require "yt/Translation.php";
	require "yt/TException.php";
	
	$translator = new Translator('trnsl.1.1.20180708T070340Z.a42043fd6fffcf8f.455f9977fdc7eaa1244ae0432cbc22becbcd606b');
	$translation = $translator->translate('Hello world', 'en-ru');
	echo $translation; // Привет мир
	echo $translation->getSource(); // Hello world;
	echo $translation->getSourceLanguage(); // en
	echo $translation->getResultLanguage(); // ru
?>