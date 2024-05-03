<?php
/* Copyright (C) 2023-2024  Anthony Damhet          <contact@progiseize.fr>*/

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

// Droits
if (!$user->rights->doliedition->edition->read) : accessforbidden(); 
endif;

// ON CHARGE LES FICHIERS NECESSAIRES
dol_include_once('/doliedition/class/doliedition.class.php');

// ON CHARGE LA LANGUE DU MODULE
$langs->load("doliedition@doliedition");

/*******************************************************************
* VARIABLES
********************************************************************/
$action = GETPOST('action', 'alphanohtml');
$editionid = GETPOST('editionid', 'int');

$form = new Form($db);

$edition_static = new DoliEdition($db);
$list_editions = $edition_static->fetch_all();

$last_edition_obj = reset($list_editions);
$last_edition = intval($last_edition_obj->edition);
$last_numero = intval($last_edition_obj->numero);
$error = 0;

$view = ($action=='addedition')?$action:'';

/*******************************************************************
* ACTIONS
********************************************************************/

switch ($action):

case 'add_edition_confirm':

    if(GETPOST('token') == $_SESSION['token']) :

        if(!GETPOST('new-number', 'int')) : 
            $error++;
            setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Numero')), '', 'errors');
        endif;

        if(!GETPOST('new-edition', 'alphanohtml')) : 
            $error++;
            setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('doliEditionE')), '', 'errors');
        endif;

        if(!GETPOST('new-datestart', 'alphanohtml')) : 
            $error++;
            setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('DateStart')), '', 'errors');
        endif;

        if(!GETPOST('new-datestop', 'alphanohtml')) : 
            $error++;
            setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('DateEnd')), '', 'errors');
        endif;

        if(GETPOST('new-datestart', 'alphanohtml') > GETPOST('new-datestop', 'alphanohtml')) :
            $error++;
            setEventMessages($langs->transnoentities('doliEditionDateReversed'), '', 'errors');
        endif;

        if(!$error) :

            $new_edition = new DoliEdition($db);
            $new_edition->edition = GETPOST('new-edition', 'alphanohtml');
            $new_edition->numero = GETPOST('new-number', 'int');
            $new_edition->date_debut = GETPOST('new-datestart', 'alphanohtml');
            $new_edition->date_fin = GETPOST('new-datestop', 'alphanohtml');
            $new_edition->note = GETPOST('new-note', 'alphanohtml');

            if(GETPOSTISSET('setcurrent') && GETPOST('setcurrent', 'int')) :
                $new_edition->active = 1;
                $new_edition->current = 1;
            endif;

            if($new_edition->create($user)) :
                $list_editions = $edition_static->fetch_all();
                setEventMessages($langs->trans('doliEditionAddSuccess'), '', 'mesgs');
                else:
                    setEventMessages($langs->trans('doliEditionErrorOccurred'), '', 'errors');
                endif;
            else: $action = 'addedition';
            endif;


        else: setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'), '', 'warnings');
        endif;
    break;

    //
case 'seteditionactive':

    if(GETPOST('token') == $_SESSION['token']) :

        if(empty($editionid)) : setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('ID')), '', 'errors'); 
        endif;

        if(!$error) : 
            if($list_editions[$editionid]->setActive()) : setEventMessages($langs->transnoentities('Activated'), '', 'mesgs'); $list_editions = $edition_static->fetch_all();
                else: setEventMessages($langs->transnoentities('Error'), '', 'errors'); 
                endif;
        endif;

        else: setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'), '', 'warnings');
        endif;
    break;

    //
case 'seteditioninactive':

    if(GETPOST('token') == $_SESSION['token']) :

        if(empty($editionid)) : setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('ID')), '', 'errors'); 
        endif;

        if(!$error) : 
            if($list_editions[$editionid]->setInactive()) : setEventMessages($langs->transnoentities('Disabled'), '', 'mesgs'); $list_editions = $edition_static->fetch_all();
                else: setEventMessages($langs->transnoentities('Error'), '', 'errors'); 
                endif;
        endif;

        else: setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'), '', 'warnings');
        endif;
    break;

    //
case 'seteditioncurrent_confirm':

    if(GETPOST('token') == $_SESSION['token']) :

        if(empty($editionid)) : $error++; setEventMessages($langs->trans('ErrorFieldRequired', $langs->trans('doliEditionE')), '', 'errors'); 
        endif;

        if(!$error && GETPOST('confirm', 'alphanohtml') == 'yes') :
            if($list_editions[$editionid]->setCurrent()) : setEventMessages($langs->transnoentities('doliEditionCurrentUpdated'), '', 'mesgs'); $list_editions = $edition_static->fetch_all();
                else: setEventMessages($langs->transnoentities('Error'), '', 'errors'); 
                endif;
        endif;
        else: setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'), '', 'warnings');
        endif;
    break;

    //
case 'deleteedition_confirm':

    if(!$user->rights->doliedition->edition->delete) : setEventMessages($langs->transnoentities('NotEnoughPermissions'), '', 'errors'); break; 
    endif;
    if(empty($editionid)) : $error++; setEventMessages($langs->trans('ErrorFieldRequired', $langs->trans('doliEditionE')), '', 'errors'); 
    endif;

    if(!$error && GETPOST('confirm', 'alphanohtml') == 'yes') :

        $checkdelete = $edition_static->delete_edition($editionid, $user);
        if($checkdelete > 0) : 
            setEventMessages($langs->transnoentities('Delete'), '', 'mesgs'); $list_editions = $edition_static->fetch_all();
            elseif($checkdelete == -1) : setEventMessages($langs->transnoentities('NotEnoughPermissions'), '', 'errors');
            elseif($checkdelete == -2) : setEventMessages($langs->transnoentities('ErrorSQL'), '', 'errors');
            endif;
    endif;
    break;

    //
case 'editeditionconfirm':

    if(!$user->rights->doliedition->edition->write) : setEventMessages($langs->transnoentities('NotEnoughPermissions'), '', 'errors'); break; 
    endif;

    $editnumber = GETPOST('editnumber', 'int');
    $editedition = GETPOST('editedition', 'alphanohtml');
    $editdatestart = GETPOST('editdatestart', 'alphanohtml');
    $editdatestop = GETPOST('editdatestop', 'alphanohtml');
    $editnote = GETPOST('editnote', 'alphanohtml');

    if(empty($editnumber)) :
        $error++;
        setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Numero')), '', 'errors');
    endif;

    if(empty($editedition)) :
        $error++;
        setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('doliEditionE')), '', 'errors');
    endif;

    if(empty($editdatestart)) :
        $error++;
        setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('DateStart')), '', 'errors');
    endif;

    if(empty($editdatestop)) : 
        $error++;
        setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('DateEnd')), '', 'errors');
    endif;

    if($editdatestart > $editdatestop) :
        $error++;
        setEventMessages($langs->transnoentities('doliEditionDateReversed'), '', 'errors');
    endif;

    if(!$error) :

            $list_editions[$editionid]->numero = $editnumber;
            $list_editions[$editionid]->edition = $editedition;
            $list_editions[$editionid]->date_debut = $editdatestart;
            $list_editions[$editionid]->date_fin = $editdatestop;
            $list_editions[$editionid]->note = $editnote;

            $checkupdate = $list_editions[$editionid]->update($user);
        if($checkupdate > 0) : 
            setEventMessages($langs->transnoentities('Update'), '', 'mesgs'); $list_editions = $edition_static->fetch_all();
            elseif($checkupdate == -1) : setEventMessages($langs->transnoentities('NotEnoughPermissions'), '', 'errors');
                elseif($checkupdate == -2) : setEventMessages($langs->transnoentities('ErrorSQL'), '', 'errors');
                endif;
                
                $list_editions[$editionid]->refresh();
                
        else: $action = 'editedition';
        endif;

    break;

endswitch;

$is_active_edition = false;
if(!empty($list_editions)) :
    foreach($list_editions as $ed):
        if($ed->active) : $is_active_edition = true; 
        endif;
    endforeach;
endif;


/***************************************************
* VIEW
****************************************************/
$array_js = array();
$array_css = array('doliedition/css/doliedition.css');

llxHeader('', $langs->trans('doliEditionManage'), '', '', '', '', $array_js, $array_css); 

// CONFIRMATION FORMULAIRES
if($action == 'seteditioncurrent') :

    $error = 0;

    if(empty($editionid)) : $error++; 
    endif;
    if(!$error) : 

        echo $form->formconfirm(
            $_SERVER["PHP_SELF"].'?editionid='.$editionid,
            $langs->trans('Confirm'),
            $langs->trans('doliEditionSetCurrentConfirm', $list_editions[$editionid]->edition),
            'seteditioncurrent_confirm',
            array(), '', 1, 250, 520, 0
        );
    endif;
elseif($action == 'deleteedition' && $user->rights->doliedition->edition->delete) :

    $error = 0;
    if(empty($editionid)) : $error++; 
    endif;
    if(!$error) :
        $editionid = GETPOST('editionid', 'int');
        echo $form->formconfirm(
            $_SERVER["PHP_SELF"].'?editionid='.$editionid,
            $langs->trans('Confirm'),
            $langs->trans('doliEditionDeleteConfirm', $list_editions[$editionid]->edition),
            'deleteedition_confirm',
            array(), '', 1, 250, 520, 0
        );
    endif;
endif; ?>

<div class="doliedition">

    <h1><i class="far fa-calendar-alt"></i> <?php echo $langs->transnoentities('doliEditionManage'); ?></h1>
    <div class="justify opacitymedium" style="margin: 0 0 24px 0;"><?php echo img_info().' '.$langs->trans("doliEditionManageDesc"); ?></div>

    <?php if(!$is_active_edition) : ?>
        <div class="doliedition-top-message msg-warning">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $langs->transnoentities('doliEditionNeeded'); ?>
        </div>
    <?php endif; ?>

    <div class="doliedition-card with-topmenu">

        <?php // TOP MENU
        $loginmenu = array();
        $loginmenu[] = array('action' => '', 'icon' => 'fas fa-list','title' => $langs->trans('doliEditionList'));
        if($user->rights->doliedition->edition->write) :
            $loginmenu[] = array('action' => 'addedition', 'icon' => 'fas fa-plus-circle','title' => $langs->trans('Add'));
        endif; ?>
        <nav class="doliedition-card-topmenu">
            <ul>
                <?php foreach ($loginmenu as $menukey => $menudet): ?>
                    <li class="<?php echo ($view == $menudet['action'])?'active':''; ?>">
                        <?php $morelink = (!empty($menudet['action']))?'?action='.$menudet['action'].'&token='.newToken():''; ?>
                        <a href="<?php echo $_SERVER['PHP_SELF'].$morelink; ?>">
                            <i class="<?php echo $menudet['icon']; ?>"></i> <?php echo $menudet['title']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="doliedition-params-title"><?php echo $langs->trans('doliEditionList'); ?></div>
        <div class="doliedition-card-content paddingtop">
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="doliedition-form">

                <?php if($action == 'addedition'): $nextaction = 'add_edition_confirm';
                else: $nextaction = 'editeditionconfirm'; endif; ?>
                <input type="hidden" name="action" value="<?php echo $nextaction; ?>">
                <input type="hidden" name="token" value="<?php echo newtoken(); ?>">

                <table class="doliedition-table-simple doledition-table">
                    <tbody>
                        <tr class="">
                            <th class="center nowrap width25"></th>
                            <th><?php echo $langs->trans('doliEditionE'); ?></th>  
                            <th><?php echo $langs->trans('DateStart'); ?></th>
                            <th><?php echo $langs->trans('DateEnd'); ?></th>                
                            <th><?php echo $langs->trans('Note'); ?></th>
                            <th class="right nowrap width25"><?php echo $langs->trans('doliEditionNumberShort'); ?></th>
                            <th class="center nowrap width50"><?php echo $langs->trans('Active'); ?></th>                            
                            <th class="right nowrap width25"></th>
                        </tr>
                        <?php
                        if($action == 'addedition'): ?>
                        <tr>
                            <td class="center nowrap width25"></td>
                            <td class="width100"><input type="text" name="new-edition" class="maxwidth150" value="<?php echo GETPOST('new-edition')?:''; ?>"></td>
                            <td class="width150"><input type="date" name="new-datestart" id="new-datestart" value="<?php echo GETPOST('new-datestart')?:''; ?>" class=""></td>
                            <td class="width150"><input type="date" name="new-datestop" id="new-datestop" value="<?php echo GETPOST('new-datestop')?:''; ?>" class=""></td>
                            <td><input type="text" name="new-note" class="minwidth300" value="<?php echo GETPOST('new-note')?:''; ?>"></td>
                            <td class="right nowrap width25"><input type="number" name="new-number" value="<?php echo $last_numero + 1; ?>" min="1" step="1" class="maxwidth75"></td>
                            <td class="center nowrap width50"></td>
                            <td class="nowrap right width25">
                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="doliedition-btn btn-danger btn-sm" ><?php echo $langs->trans('Cancel'); ?></a>
                                <input type="submit" class="doliedition-btn btn-sm btn-secondary">
                            </td>
                        </tr>
                        <?php endif;
                        ?>
                        <?php foreach($list_editions as $edition): ?>

                            <?php if($action == 'editedition' && $editionid == $edition->id) : ?>
                                <tr class="">
                                    <td class="center nowrap width25"></td>
                                    
                                    <td class="">
                                        <input type="text" name="editedition" class="minwidth150" value="<?php echo $edition->edition; ?>">
                                    </td>
                                    <td>
                                        <input type="date" name="editdatestart" id="editdatestart" value="<?php echo $edition->date_debut->format('Y-m-d'); ?>" class="">
                                    </td>
                                    <td>
                                        <input type="date" name="editdatestop" id="editdatestop" value="<?php echo $edition->date_fin->format('Y-m-d'); ?>" class="">
                                    </td>
                                    <td class="">
                                        <input type="text" name="editnote" class="minwidth300" value="<?php echo $edition->note; ?>">
                                    </td>
                                    <td class="right nowrap width25">
                                        <input type="hidden" name="editionid" value="<?php echo $edition->id; ?>">
                                        <input type="number" name="editnumber" value="<?php echo $edition->numero; ?>" min="1" class="maxwidth75" step="1">
                                    </td>
                                    <td class="center nowrap width50"></td>
                                    <td class="center nowrap width25">
                                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="doliedition-btn btn-danger btn-sm" ><?php echo $langs->trans('Cancel'); ?></a>
                                        <input type="submit" class="doliedition-btn btn-sm btn-secondary">
                                    </td>
                                </tr>

                            <?php else: ?>
                            
                                <tr class="">
                                    
                                    <td class="center nowrap width25">
                                        <?php 
                                        if($edition->current) : $color_edition = 'coloredition-current';
                                        elseif($edition->active && !$edition->current) : $color_edition = 'coloredition-active';
                                        else: $color_edition = 'coloredition-inactive';
                                        endif;                                         
                                        $iconedition = '<i class="fas fa-star paddingright '.$color_edition.'" style="font-size:0.95em; vertical-align: middle !important;"></i>';

                                        if($edition->active && !$edition->current):
                                            echo '<a href="'.$_SERVER["PHP_SELF"].'?editionid='.$edition->id.'&action=seteditioncurrent&token='.newtoken().'">'.$iconedition.'</a>';
                                        elseif($edition->active): echo $iconedition;
                                        endif; ?>
                                    </td>
                                    <td class="smbold width100"> <?php echo $edition->edition; ?></td>                                    
                                    <td class="width150"><?php echo $edition->date_debut->format('d/m/Y'); ?></td>
                                    <td class="width150"><?php echo $edition->date_fin->format('d/m/Y'); ?></td>
                                    
                                    <td class=""><?php echo $edition->note; ?></td>
                                    <td class="right nowrap smbold width25"><?php echo $edition->numero; ?></td>
                                    <td class="center nowrap width50">
                                        <?php if($edition->active) : echo '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?editionid='.$edition->id.'&action=seteditioninactive&token='.newtoken().'">'.img_picto($langs->trans("Activated"), 'switch_on').'</a>';
                                        else: echo '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?editionid='.$edition->id.'&action=seteditionactive&token='.newtoken().'">'.img_picto($langs->trans("Disabled"), 'switch_off').'</a>'; 
                                        endif; ?>
                                    </td>
                                    
                                    <td class="right nowrap width25">
                                        <?php if($user->rights->doliedition->edition->write) : ?>
                                            <a href="<?php echo $_SERVER['PHP_SELF'].'?editionid='.$edition->id.'&action=editedition&token='.newtoken(); ?>" class="doliedition-editlink" style="margin:0 3px;"><i class="fas fa-pencil-alt"></i></a>
                                        <?php endif; ?>
                                        <?php if($user->rights->doliedition->edition->delete) : ?>
                                            <a href="<?php echo $_SERVER['PHP_SELF'].'?editionid='.$edition->id.'&action=deleteedition&token='.newtoken(); ?>" class="doliedition-editlink" style="margin:0 3px;"><i class="fas fa-trash-alt"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
        
    </div>

    
    

    
    
</div>


<?php dol_fiche_end(); llxFooter(); $db->close(); ?>
