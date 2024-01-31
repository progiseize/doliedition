<?php
/* Copyright (C) 2023-2024  Anthony Damhet          <contact@progiseize.fr>*/

class DoliEdition
{

    public $id;
    public $edition;
    public $numero;
    public $active = 0;
    public $current = 0;
    public $note;

    public $date_debut;
    public $date_fin;
    public $date_range;
    public $date_array = array();    
    public $weeknumber;    

    public $db;
    public $table_editions = 'doliedition';

    public function __construct($db)
    {
        $this->db = $db;
    }

    //
    public function create($user)
    {

        global $conf;
        
        if(!$this->numero) : return false; 
        endif;
        if(!$this->edition) : return false; 
        endif;
        if(!$this->date_debut) : return false; 
        endif;
        if(!$this->date_fin) : return false; 
        endif;

        $this->db->begin();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_editions;
        $sql.= " (entity,edition,numero,debut,fin,note,active,current) VALUES (";
        $sql.= " '".$conf->entity."'";
        $sql.= ", '".$this->db->escape($this->edition)."'";
        $sql.= ", '".$this->db->escape($this->numero)."'";
        $sql.= ", '".$this->db->escape($this->date_debut)."'";
        $sql.= ", '".$this->db->escape($this->date_fin)."'";
        $sql.= ", '".$this->db->escape($this->note)."'";
        $sql.= ", '".$this->db->escape($this->active)."'";
        $sql.= ", '".$this->db->escape($this->current)."'";
        $sql.= " )";
        $res = $this->db->query($sql);

        if($res) : 
            $insert_id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_editions);
            $this->db->commit(); return $insert_id;
        else: $this->db->rollback(); return false;
        endif;
    }

    //
    public function fetch($id)
    {

        $sql = "SELECT * FROM ".MAIN_DB_PREFIX.$this->table_editions;
        $sql .= " WHERE rowid = '".$id."'";

        $result = $this->db->query($sql);
        $obj = $this->db->fetch_object($result);

        if($result->num_rows == 0) : return -1;
        else:

            $this->id = $obj->rowid;
            $this->edition = $obj->edition;
            $this->active = $obj->active;
            $this->current = $obj->current;
            $this->note = $obj->note;

            $this->numero = $obj->numero;
            $this->date_debut = date_create($obj->debut);
            $this->date_fin = date_create($obj->fin.' 22:00:00'); // On met une heure pour que le dernier jour soit pris en compte dans DatePeriod        

            $interval = DateInterval::createFromDateString('1 day');

            // DatePeriod
            $this->date_range = new DatePeriod($this->date_debut, $interval, $this->date_fin);

            // ARRAY DATE_US => DATE_FR
            foreach($this->date_range as $d): $this->date_array[$d->format('Y-m-d')] = $d->format('d/m/Y'); 
            endforeach;

            // NUMERO SEMAINE
            $this->weeknumber = $this->date_debut->format('W');

            return $this->id;

        endif;
    }

    //
    public function refresh()
    {

        $this->fetch($this->id);
        return $this->id;
    }

    //
    public function fetchByYear($edition)
    {

        global $conf;

        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_editions;
        $sql.= " WHERE entity = '".$conf->entity."' AND edition = '".$edition."'";
        $res = $this->db->query($sql);

        if(!$res) : return -1; 
        endif;
        if($res->num_rows == 0) : return 0; 
        endif;

        $obj = $this->db->fetch_object($res);
        return $this->fetch($obj->rowid);
    }

    //
    public function fetch_all()
    {

        global $conf;

        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_editions;
        $sql.= " WHERE entity = '".$conf->entity."'";
        $sql.= " ORDER BY debut DESC";
        $result = $this->db->query($sql);

        $list_editions = array();

        if($result) :
            while($obj = $this->db->fetch_object($result)):
                $edition = new self($this->db);
                $edition->fetch($obj->rowid);
                $list_editions[$obj->rowid] = $edition;
            endwhile;           
        else: dol_print_error($edition->db); 
        endif;

        return $list_editions;
    }

    //
    public function delete_edition($edition_id,$user):int
    {

        if(!$user->rights->doliedition->edition->delete) : return -1; 
        endif;
        $sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_editions;
        $sql.= " WHERE rowid = '".$this->db->escape($edition_id)."'";
        $res = $this->db->query($sql);
        if(!$res) : return -2; 
        endif;
        return 1;
    }

    public function update($user):int
    {

        if(!$user->rights->doliedition->edition->write) : return -1; 
        endif;

        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_editions;
        $sql.= " SET numero = '".$this->db->escape($this->numero)."'";
        $sql.= ", edition = '".$this->db->escape($this->edition)."'";
        $sql.= ", debut = '".$this->db->escape($this->date_debut)."'";
        $sql.= ", fin = '".$this->db->escape($this->date_fin)."'";
        $sql.= ", note = '".$this->db->escape($this->note)."'";
        $sql.= " WHERE rowid = '".$this->id."'";
        $res = $this->db->query($sql);

        if(!$res) : return -2; 
        endif;
        return 1;
    }

    //
    public function getCurrentEdition($mode = 'object')
    {

        global $conf;

        if($mode == 'object') : 
            $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_editions;
            $sql .= " WHERE current = '1' AND entity = '".$conf->entity."'";

            $result = $this->db->query($sql);
            $obj = $this->db->fetch_object($result);

            if($result->num_rows == 0) : return -1;
            else:
                $next_edition = new Self($this->db);
                $next_edition->fetch($obj->rowid);
                return $next_edition;
            endif;
            
        elseif($mode == 'year') : 

            $sql = "SELECT edition FROM ".MAIN_DB_PREFIX.$this->table_editions;
            $sql .= " WHERE current = '1' AND entity = '".$conf->entity."'";

            $result = $this->db->query($sql);
            $obj = $this->db->fetch_object($result);

            if($result->num_rows == 0) : return -1;
            else: return $obj->edition;
            endif;
            
        endif;
    }

    //
    public function getEditionsActive($mode = 'object')
    {

        global $conf;

        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_editions;
        $sql .= " WHERE active = '1' AND entity = '".$conf->entity."'";
        $sql .= " ORDER BY debut ASC";
        $result = $this->db->query($sql);

        $list_editions = array();

        if($result) :
            while($obj = $this->db->fetch_object($result)):
                $edition = new self($this->db);
                $edition->fetch($obj->rowid);
                if($mode == 'object') : $list_editions[$obj->rowid] = $edition;
                elseif($mode == 'array') : array_push($list_editions, $edition->edition);
                endif;
            endwhile;           
        else: dol_print_error($edition->db); 
        endif;

        return $list_editions;
    }

    //
    public function getXLast($limit = 3,$sortorder = 'DESC')
    {

        global $conf;

        $sql = "SELECT edition FROM ".MAIN_DB_PREFIX.$this->table_editions;
        $sql.= " WHERE entity = '".$conf->entity."'";
        $sql.= " ORDER BY debut DESC LIMIT ".$limit;
        $result = $this->db->query($sql);

        $list_editions = array();

        if($result) :
            while($obj = $this->db->fetch_object($result)):
                array_push($list_editions, $obj->edition);
            endwhile;           
        else: dol_print_error($edition->db); 
        endif;

        switch ($sortorder):
        case 'ASC': asort($list_editions); 
            break;            
        case 'DESC': default: arsort($list_editions); 
            break;            
        endswitch;
        

        return $list_editions;
    }

    //
    public function setActive()
    {

        if(!$this->id) : return false; 
        endif;

        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_editions;
        $sql.= " SET active = '1' WHERE rowid = '".$this->id."'";
        $result = $this->db->query($sql);

        if(!$result) : return false; 
        endif;
        return true;
    }

    //
    public function setInactive()
    {

        if(!$this->id) : return false; 
        endif;

        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_editions;
        $sql.= " SET active = '0' WHERE rowid = '".$this->id."'";
        $result = $this->db->query($sql);

        if(!$result) : return false; 
        endif;
        return true;
    }

    //
    public function setCurrent()
    {

        global $conf;

        if(!$this->id) : return false; 
        endif;

        $this->db->begin();

        $sql_remove_current = "UPDATE ".MAIN_DB_PREFIX.$this->table_editions;
        $sql_remove_current.= " SET current = '0'";
        $sql_remove_current.= " WHERE entity = '".$conf->entity."' AND current = '1'";

        $res_remove_current = $this->db->query($sql_remove_current);
        if(!$res_remove_current) : $this->db->rollback(); return false; 
        endif;

        $sql_set_current = "UPDATE ".MAIN_DB_PREFIX.$this->table_editions;
        $sql_set_current.= " SET current = '1'";
        $sql_set_current.= " WHERE rowid = '".$this->id."'";

        $res_set_current = $this->db->query($sql_set_current);
        if(!$res_set_current) : $this->db->rollback(); return false; 
        endif;

        $this->db->commit();
        return true;
    }


}

?>
