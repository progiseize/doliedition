<?php
/* Copyright (C) 2023-2024 	Anthony Damhet			<contact@progiseize.fr>*/

class ActionsDoliEdition
{

 

    /**
     * showOptionals
     *
     * @param  array        $parameters  Hook metadatas (context, etc...)
     * @param  CommonObject $object      The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param  string       $action      Current action (if set). Generally create or edit or null
     * @param  HookManager  $hookmanager Hook manager propagated to allow calling another hook
     * @return int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function showOptionals($parameters, &$object, &$action, $hookmanager)
    {

        global $conf, $user, $langs, $db;

        $contexts = explode(':', $parameters['context']);

        // Extrafields Lines AUTO

        $majextraline_contexts = array('ordersuppliercard','invoicesuppliercard');
        $res = array_intersect($majextraline_contexts, $contexts);
        if(!empty($res) && $parameters['display_type'] == 'line' && $parameters['mode'] == 'create' ) :

            switch ($object->element):
            case 'commande_fournisseurdet': 
                include_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
                $objectparent = new CommandeFournisseur($db);
                break;
            case 'facture_fourn_det': 
                include_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
                $objectparent = new FactureFournisseur($db);
                break;
            default: 
                return 0; break;
            endswitch;

            $id = GETPOST('id', 'int');            
            $objectparent->fetch($id);

            $line_edition = '';
            if(isset($objectparent->array_options['options_edition_num']) && !empty($objectparent->array_options['options_edition_num']) && intval($objectparent->array_options['options_edition_num']) > 0) :
                $line_edition = $objectparent->array_options['options_edition_num'];
            endif;
            if(empty($line_edition)) :
                dol_include_once('/doliedition/class/doliedition.class.php');
                $editionstatic = new DoliEdition($db);
                $line_edition = $editionstatic->getCurrentEdition('year');
            endif;
            $_POST['options_edition_num'] = $line_edition;
        endif;

        return 0;
    }

}

?>
