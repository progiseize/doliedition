<?php
/* 
 * Copyright (C) 2021 Anthony Damhet - Progiseize <a.damhet@progiseize.fr>
*/

$res=0;
if (! $res && file_exists("../main.inc.php")): $res=@include '../main.inc.php'; endif;
if (! $res && file_exists("../../main.inc.php")): $res=@include '../../main.inc.php'; endif;
if (! $res && file_exists("../../../main.inc.php")): $res=@include '../../../main.inc.php'; endif;

// Protection if external user
if ($user->socid > 0): accessforbidden(); endif;

// Droits
if (!$user->rights->doliedition->edition->read): accessforbidden(); endif;

// ON CHARGE LES FICHIERS NECESSAIRES

dol_include_once('/progilib/class/progiform.class.php');
dol_include_once('/doliedition/class/doliedition.class.php');

// ON CHARGE LA LANGUE DU MODULE
$langs->load("doliedition@doliedition");

/*******************************************************************
* VARIABLES
********************************************************************/
$action = GETPOST('action','alphanohtml');
$editionid = GETPOST('editionid','int');

$magicform = new ProgiForm($db);

$edition_static = new DoliEdition($db);
$list_editions = $edition_static->fetch_all();

$last_edition_obj = reset($list_editions);
$last_edition = intval($last_edition_obj->edition);
$last_numero = intval($last_edition_obj->numero);
$error = 0;

/*******************************************************************
* ACTIONS
********************************************************************/

switch ($action):

    //
    case 'add_edition_confirm':

        if(GETPOST('token') == $_SESSION['token']):

            if(!GETPOST('new-number','int')): 
                $error++; array_push($magicform->error_fields,'new-number');
                setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('Numero')),'','errors');
            endif;

            if(!GETPOST('new-edition','alphanohtml')): 
                $error++; array_push($magicform->error_fields,'new-edition');
                setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('doliEditionE')),'','errors');
            endif;

            if(!GETPOST('new-datestart','alphanohtml')): 
                $error++; array_push($magicform->error_fields,'new-datestart');
                setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('DateStart')),'','errors');
            endif;

            if(!GETPOST('new-datestop','alphanohtml')): 
                $error++; array_push($magicform->error_fields,'new-datestop');
                setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('DateEnd')),'','errors');
            endif;

            if(GETPOST('new-datestart','alphanohtml') > GETPOST('new-datestop','alphanohtml')):
                $error++; array_push($magicform->error_fields,'new-datestart'); array_push($magicform->error_fields,'new-datestop');
                setEventMessages($langs->transnoentities('doliEditionDateReversed'),'','errors');
            endif;

            if(!$error):

                $new_edition = new DoliEdition($db);
                $new_edition->edition = GETPOST('new-edition','alphanohtml');
                $new_edition->numero = GETPOST('new-number','int');
                $new_edition->date_debut = GETPOST('new-datestart','alphanohtml');
                $new_edition->date_fin = GETPOST('new-datestop','alphanohtml');
                $new_edition->note = GETPOST('new-note','alphanohtml');

                if(GETPOSTISSET('setcurrent') && GETPOST('setcurrent','int')):
                    $new_edition->active = 1;
                    $new_edition->current = 1;
                endif;

                if($new_edition->create($user)):
                    $list_editions = $edition_static->fetch_all();
                    setEventMessages($langs->trans('doliEditionAddSuccess'),'','mesgs');
                else:
                    setEventMessages($langs->trans('doliEditionErrorOccurred'),'','errors');
                endif;
            else: $action = 'add_edition';
            endif;


        else: setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'),'','warnings');
        endif;
    break;

    //
    case 'seteditionactive':

        if(GETPOST('token') == $_SESSION['token']):

            if(empty($editionid)): setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('ID')),'','errors'); endif;

            if(!$error): 
                if($list_editions[$editionid]->setActive()): setEventMessages($langs->transnoentities('Activated'),'','mesgs'); $list_editions = $edition_static->fetch_all();
                else: setEventMessages($langs->transnoentities('Error'),'','errors'); endif;
            endif;

        else: setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'),'','warnings');
        endif;
    break;

    //
    case 'seteditioninactive':

        if(GETPOST('token') == $_SESSION['token']):

            if(empty($editionid)): setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('ID')),'','errors'); endif;

            if(!$error): 
                if($list_editions[$editionid]->setInactive()): setEventMessages($langs->transnoentities('Disabled'),'','mesgs'); $list_editions = $edition_static->fetch_all();
                else: setEventMessages($langs->transnoentities('Error'),'','errors'); endif;
            endif;

        else: setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'),'','warnings');
        endif;
    break;

    //
    case 'seteditioncurrent_confirm':

        if(GETPOST('token') == $_SESSION['token']):

            if(empty($editionid)): $error++; setEventMessages($langs->trans('ErrorFieldRequired',$langs->trans('doliEditionE')),'','errors'); endif;

            if(!$error && GETPOST('confirm','alphanohtml') == 'yes'):
                if($list_editions[$editionid]->setCurrent()): setEventMessages($langs->transnoentities('doliEditionCurrentUpdated'),'','mesgs'); $list_editions = $edition_static->fetch_all();
                else: setEventMessages($langs->transnoentities('Error'),'','errors'); endif;
            endif;
        else: setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'),'','warnings');
        endif;
    break;

    //
    case 'deleteedition_confirm':

        if(!$user->rights->doliedition->edition->delete): setEventMessages($langs->transnoentities('NotEnoughPermissions'),'','errors'); break; endif;
        if(empty($editionid)): $error++; setEventMessages($langs->trans('ErrorFieldRequired',$langs->trans('doliEditionE')),'','errors'); endif;

        if(!$error && GETPOST('confirm','alphanohtml') == 'yes'):

            $checkdelete = $edition_static->delete_edition($editionid,$user);
            if($checkdelete > 0): 
                setEventMessages($langs->transnoentities('Delete'),'','mesgs'); $list_editions = $edition_static->fetch_all();
            elseif($checkdelete == -1): setEventMessages($langs->transnoentities('NotEnoughPermissions'),'','errors');
            elseif($checkdelete == -2): setEventMessages($langs->transnoentities('ErrorSQL'),'','errors');
            endif;
        endif;
    break;

    //
    case 'editeditionconfirm':

        if(!$user->rights->doliedition->edition->write): setEventMessages($langs->transnoentities('NotEnoughPermissions'),'','errors'); break; endif;

        $editnumber = GETPOST('editnumber','int');
        $editedition = GETPOST('editedition','alphanohtml');
        $editdatestart = GETPOST('editdatestart','alphanohtml');
        $editdatestop = GETPOST('editdatestop','alphanohtml');
        $editnote = GETPOST('editnote','alphanohtml');

        if(empty($editnumber)):
            $error++; array_push($magicform->error_fields,'edit-number');
            setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('Numero')),'','errors');
        endif;

        if(empty($editedition)):
            $error++; array_push($magicform->error_fields,'edit-edition');
            setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('doliEditionE')),'','errors');
        endif;

        if(empty($editdatestart)):
            $error++; array_push($magicform->error_fields,'edit-datestart');
            setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('DateStart')),'','errors');
        endif;

        if(empty($editdatestop)): 
            $error++; array_push($magicform->error_fields,'edit-datestop');
            setEventMessages($langs->transnoentities('ErrorFieldRequired',$langs->transnoentities('DateEnd')),'','errors');
        endif;

        if($editdatestart > $editdatestop):
            $error++; array_push($magicform->error_fields,'edit-datestart'); array_push($magicform->error_fields,'edit-datestop');
            setEventMessages($langs->transnoentities('doliEditionDateReversed'),'','errors');
        endif;

        if(!$error):

                $list_editions[$editionid]->numero = $editnumber;
                $list_editions[$editionid]->edition = $editedition;
                $list_editions[$editionid]->date_debut = $editdatestart;
                $list_editions[$editionid]->date_fin = $editdatestop;
                $list_editions[$editionid]->note = $editnote;

                $checkupdate = $list_editions[$editionid]->update($user);
                if($checkupdate > 0): 
                    setEventMessages($langs->transnoentities('Update'),'','mesgs'); $list_editions = $edition_static->fetch_all();
                elseif($checkupdate == -1): setEventMessages($langs->transnoentities('NotEnoughPermissions'),'','errors');
                elseif($checkupdate == -2): setEventMessages($langs->transnoentities('ErrorSQL'),'','errors');
                endif;
                
                $list_editions[$editionid]->refresh();
                
        else: $action = 'editedition';
        endif;

    break;

endswitch;

$is_active_edition = false;
if(!empty($list_editions)):
    foreach($list_editions as $ed):
        if($ed->active): $is_active_edition = true; endif;
    endforeach;
endif;


/***************************************************
* VIEW
****************************************************/
$array_js = array();
$array_css = array('progilib/assets/css/dolpgs.css');

llxHeader('',$langs->trans('doliEditionManage'),'','','','',$array_js,$array_css); 

// CONFIRMATION FORMULAIRES
if($action == 'seteditioncurrent'):

    $error = 0;

    if(empty($editionid)): $error++; endif;
    if(!$error): 

        echo $magicform->form->formconfirm(
            $_SERVER["PHP_SELF"].'?editionid='.$editionid,
            $langs->trans('Confirm'),
            $langs->trans('doliEditionSetCurrentConfirm',$list_editions[$editionid]->edition),
            'seteditioncurrent_confirm',
            array(),'',1,250,520,0);
    endif;
elseif($action == 'deleteedition' && $user->rights->doliedition->edition->delete):

    $error = 0;
    if(empty($editionid)): $error++; endif;
    if(!$error):
        $editionid = GETPOST('editionid','int');
        echo $magicform->form->formconfirm(
            $_SERVER["PHP_SELF"].'?editionid='.$editionid,
            $langs->trans('Confirm'),
            $langs->trans('doliEditionDeleteConfirm',$list_editions[$editionid]->edition),
            'deleteedition_confirm',
            array(),'',1,250,520,0);
    endif;


endif; ?>

<div class="dolpgs-main-wrapper doliedition">

    <h1><i class="far fa-calendar-alt"></i> <?php echo $langs->transnoentities('doliEditionManage'); ?></h1>
    <div class="justify opacitymedium" style="margin-bottom: 24px"><?php echo img_info().' '.$langs->trans("doliEditionManageDesc"); ?></div>

    <?php if($action == 'add_edition'): ?>
    <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="post" id="">

        <?php 
            echo $magicform->inputHidden('action','add_edition_confirm');
            echo $magicform->inputHidden('token',newtoken()); 
            if(empty($list_editions)): 
                echo $magicform->inputHidden('setcurrent','1'); 
            endif;
        ?>

        <h3 class="dolpgs-table-title"><?php echo $langs->trans('doliEditionAdd'); ?></h3>
        <table class="dolpgs-table">
            <tbody>

                <tr class="dolpgs-thead">
                    <th><?php echo $langs->trans('doliEditionNumberShort'); ?></th>
                    <th><?php echo $langs->trans('doliEditionE'); ?></th>
                    <th><?php echo $langs->trans('DateStart'); ?></th>
                    <th><?php echo $langs->trans('DateEnd'); ?></th>                    
                    <th><?php echo $langs->trans('Note'); ?></th>
                    <th class="right"></th>
                </tr>
                
                <tr class="dolpgs-tbody">
                    <td><?php echo $magicform->inputNumber('new-number',$last_numero+1,1); ?></td> 
                    <td><?php echo $magicform->inputText('new-edition'); ?></td>
                    <td><?php echo $magicform->inputDate('new-datestart'); ?></td>               
                    <td><?php echo $magicform->inputDate('new-datestop'); ?></td>                                       
                    <td><?php echo $magicform->inputText('new-note','','minwidth300'); ?></td>
                    <td class="right">
                        <?php echo $magicform->inputSubmit('','',$_SERVER['PHP_SELF'],'dolpgs-btn btn-sm btn-secondary'); ?>
                    </td>
                </tr>


            </tbody>
        </table>
    </form>
    <?php endif; ?>

    <div class="dolpgs-table-title-flexwrapper">
        <h3 class="dolpgs-table-title"><?php echo $langs->trans('doliEditionList'); ?></h3>
        <?php if($user->rights->doliedition->edition->write): ?>
            <a href="<?php echo $_SERVER['PHP_SELF'].'?action=add_edition&token='.newtoken(); ?>" class="dolpgs-btn btn-sm btn-primary">Ajouter</a>
        <?php endif; ?>
    </div>
    <?php if(!$is_active_edition): ?>
        <p class="dolpgs-color-danger dolpgs-font-medium"><i class="fas fa-exclamation-triangle"></i> <?php echo $langs->transnoentities('doliEditionNeeded'); ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">

        <?php echo $magicform->inputHidden('action','editeditionconfirm'); ?>
        <?php echo $magicform->inputHidden('token',newtoken()); ?>

        <table class="dolpgs-table">
            <tbody>
                <tr class="dolpgs-thead noborderside">
                    <th class="center nowrap width25"><?php echo $langs->trans('doliEditionNumberShort'); ?></th>
                    <th><?php echo $langs->trans('doliEditionE'); ?></th>
                    <th><?php echo $langs->trans('DateStart'); ?></th>
                    <th><?php echo $langs->trans('DateEnd'); ?></th>                
                    <th><?php echo $langs->trans('Note'); ?></th>
                    <th class="center nowrap width50"><?php echo $langs->trans('Active'); ?></th>
                    <th class="center nowrap width50"><?php echo $langs->trans('doliEditionCurrent'); ?></th>
                    <th class="center nowrap width25"></th>
                </tr>
                <?php foreach($list_editions as $edition): ?>

                    <?php if($action == 'editedition' && $editionid == $edition->id): ?>
                        <tr class="dolpgs-tbody">
                            <td class="center nowrap width25">
                                <?php echo $magicform->inputHidden('editionid',$edition->id); ?>
                                <?php echo $magicform->inputNumber('editnumber',$edition->numero,1,'',1,'width50'); ?>
                            </td>
                            <td class="dolpgs-font-medium"><?php echo $magicform->inputText('editedition',$edition->edition); ?></td>
                            <td><?php echo $magicform->inputDate('editdatestart',$edition->date_debut->format('Y-m-d')); ?></td>
                            <td><?php echo $magicform->inputDate('editdatestop',$edition->date_fin->format('Y-m-d')); ?></td>
                            <td class="maxwidth300"><?php echo $magicform->inputText('editnote',$edition->note,'minwidth300'); ?></td>
                            <td class="center nowrap width50"></td>
                            <td class="center nowrap width50"></td>
                            <td class="center nowrap width25">
                                <?php echo $magicform->inputSubmit('','',$_SERVER['PHP_SELF'],'dolpgs-btn btn-sm btn-secondary'); ?>
                            </td>
                        </tr>

                    <?php else: ?>
                    
                        <tr class="dolpgs-tbody">
                            <td class="center nowrap width25"><?php echo $edition->numero; ?></td>
                            <td class="dolpgs-font-medium">                    
                                <?php 
                                if($edition->current): $color_edition = 'dolpgs-color-success';
                                elseif($edition->active && !$edition->current): $color_edition = 'dolpgs-color-warning';
                                else: $color_edition = 'dolpgs-color-gray2';
                                endif; ?>
                                <i class="fas fa-circle <?php echo $color_edition; ?>" style="font-size:6px; vertical-align: middle !important;margin-right: 3px;"></i>
                                <?php echo $edition->edition; ?>                        
                            </td>
                            <td><?php echo $edition->date_debut->format('d/m/Y'); ?></td>
                            <td><?php echo $edition->date_fin->format('d/m/Y'); ?></td>
                            
                            <td class="maxwidth300"><?php echo $edition->note; ?></td>
                            <td class="center nowrap width50">
                                <?php if($edition->active): echo '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?editionid='.$edition->id.'&action=seteditioninactive&token='.newtoken().'">'.img_picto($langs->trans("Activated"), 'switch_on').'</a>';
                                else: echo '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?editionid='.$edition->id.'&action=seteditionactive&token='.newtoken().'">'.img_picto($langs->trans("Disabled"), 'switch_off').'</a>'; endif; ?>
                            </td>
                            <td class="center nowrap width50">
                                <?php if($edition->active): ?>
                                    <?php if($edition->current): ?>
                                        <i class="fas fa-star dolpgs-color-warning"></i>
                                    <?php else: ?>
                                        <a href="<?php echo $_SERVER["PHP_SELF"].'?editionid='.$edition->id.'&action=seteditioncurrent&token='.newtoken(); ?>"><i class="fas fa-star dolpgs-color-gray2"></i></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="center nowrap width25">
                                <?php if($user->rights->doliedition->edition->write): ?>
                                    <a href="<?php echo $_SERVER['PHP_SELF'].'?editionid='.$edition->id.'&action=editedition&token='.newtoken(); ?>" class="dolpgs-editlink" style="margin:0 3px;"><i class="fas fa-pencil-alt"></i></a>
                                <?php endif; ?>
                                <?php if($user->rights->doliedition->edition->delete): ?>
                                    <a href="<?php echo $_SERVER['PHP_SELF'].'?editionid='.$edition->id.'&action=deleteedition&token='.newtoken(); ?>" class="dolpgs-editlink" style="margin:0 3px;"><i class="fas fa-trash-alt"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
    
</div>


<?php dol_fiche_end(); llxFooter(); $db->close(); ?>