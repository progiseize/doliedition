<?php
/* Copyright (C) 2023-2024 	Anthony Damhet			<contact@progiseize.fr>
*/

$res=0;
if (! $res && file_exists("../main.inc.php")) : $res=@include '../main.inc.php'; 
endif;
if (! $res && file_exists("../../main.inc.php")) : $res=@include '../../main.inc.php'; 
endif;
if (! $res && file_exists("../../../main.inc.php")) : $res=@include '../../../main.inc.php'; 
endif;

// Protection if external user
if ($user->socid > 0) : accessforbidden(); 
endif;

// Droits Budget
if (!$user->rights->doliedition->edition->write) : accessforbidden(); 
endif;

// ON CHARGE LES FICHIERS NECESSAIRES
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

// ON CHARGE LA LANGUE DU MODULE
$langs->load("doliedition@doliedition");


/*******************************************************************
* VARIABLES
********************************************************************/
$action = GETPOST('action');
$error = 0;

/*******************************************************************
* ACTIONS
********************************************************************/
if($action == 'action') :
endif;

//

/***************************************************
* VIEW
****************************************************/
$array_js = array();
$array_css = array();

llxHeader('', $langs->trans('doliEditionSetupPage'), '', '', '', '', $array_js, $array_css); ?>

<div class="">
</div>


<?php dol_fiche_end(); llxFooter(); $db->close(); ?>
