<?php
class CommandsClass extends SystemClass
{


    static function SET_IMG()
    {

        $img_url = poste('img_url');
        s('img_url='.$img_url);

        //$blob = blob_create();
//$blob=file_get_contents($img_url);
// s($blob);
//$blob = 'test';
        //$params_bd = [':blob',$blob];
        $sql="update  SPR_TOV set PHOTO=:blob where kod=227001";
        s($sql);
        $blob='';
        $comData=fdb_execute_blob($sql,$img_url,$blob);
        SystemClass::$type_sql='EXECUTE';
//SystemClass::setComData($comData);
    }
    static function GET_CRON_PREDPRODZA3DAY()
    {

        // $acc = poste('acc');
        // $phone = poste('phone');
        $sql="select a.phone as phone,t1.grp,t1.kod as tov, c.kod as pred_abon,
t1.ALT_PRICE as price,
FORMATDATE(date_stop,'dd.mm.yyyy') as date_stop,a.name,
a.kod as ACC,t1.NAME as tov_name
from cb_serv_sales c, CB_ACCOUNTS a,spr_tov t1 where t1.KOD=c.tov  and
a.kod=c.ACCOUNT  
--and c.account=5363 
and tov  in (select t.kod from spr_tov t where t.grp in (select grp from PRC_GET_CHILD_GRP(309)) ) 
and  not exists(select * from cb_serv_sales c1, spr_tov t2 where t2.KOD=c1.TOV and c1.DATE_START>='today' and c1.ACCOUNT=c.ACCOUNT  
and (t2.kod  in (select t3.kod from spr_tov t3 where t3.grp in (select grp from PRC_GET_CHILD_GRP(309)) )
or  (t2.kod  in (select t3.kod from spr_tov t3 where t3.grp in (select grp from PRC_GET_CHILD_GRP(98)) ))
)    )
and price_out>0
and date_stop=cast('TODAY' as date) +3   
and a.phone is not null and strlen(a.phone)=10";
//
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }

    static function GET_CRON_PREDPRODZA1DAY()
    {

        // $acc = poste('acc');
        // $phone = poste('phone');
        $sql="select a.phone as phone,t1.grp,t1.kod as tov, c.kod as pred_abon,
t1.ALT_PRICE as price,
FORMATDATE(date_stop,'dd.mm.yyyy') as date_stop,a.name,
a.kod as ACC,t1.NAME as tov_name
from cb_serv_sales c, CB_ACCOUNTS a,spr_tov t1 where t1.KOD=c.tov  and
a.kod=c.ACCOUNT  
--and c.account=5363 
and tov  in (select t.kod from spr_tov t where t.grp in (select grp from PRC_GET_CHILD_GRP(309)) ) 
and  not exists(select * from cb_serv_sales c1, spr_tov t2 where t2.KOD=c1.TOV and c1.DATE_START>='today' and c1.ACCOUNT=c.ACCOUNT  
and (t2.kod  in (select t3.kod from spr_tov t3 where t3.grp in (select grp from PRC_GET_CHILD_GRP(309)) )
or  (t2.kod  in (select t3.kod from spr_tov t3 where t3.grp in (select grp from PRC_GET_CHILD_GRP(98)) ))
)    )
and price_out>0
and date_stop='tomorrow'
and a.phone is not null and strlen(a.phone)=10";
//
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }
    static function GET_TRAININGS_VIDPRAC_ACC()
    {
        $acc = poste('acc');
        $dbeg = poste('dbeg');
        $dend = poste('dend');
        $sql="select  dat,FORMATDATE(dat,'dd.mm.yyyy') as dat1,TIME_FROM,TIME_TO, CLIENTS_CNT,
(select name from spr_sotr where kod=sotr) as sotr_name ,
(select name from spr_tov where kod=tov) as tov_name
 from CB_TRAININGS t,CB_TRANING_LIST l where
        l.TRANING=t.kod and   l.ACCOUNT=".$acc." and
  dat between  '".$dbeg."' and '".$dend."' and (tov=192002 or tov=3518) order by dat, time_from";
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }

    static function GET_TRAININGS_ALL_VIDPRAC_ZR()
    {

        $acc = poste('acc');
        $dat = poste('dat');
        $sql="select  kod,dat,FORMATDATE(dat,'dd.mm.yyyy') as dat1, TIME_FROM,TIME_TO, CLIENTS_CNT,
(select name from spr_sotr where kod=sotr) as sotr_name ,
(select name from spr_tov where kod=tov) as tov_name,
  COALESCE((select first 1 l.ACCOUNT  from CB_TRANING_LIST l where l.TRANING=t.kod and l.ACCOUNT=".$acc."),0)  as acc,
       (select  count(*)  from CB_TRANING_LIST l where l.TRANING=t.kod )  as cntTran 
 from CB_TRAININGS t where dat between  'today' and '".$dat."' and tov=3518 order by dat, time_from";
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }

    static function GET_TRAININGS_ALL_VIDPRAC()
    {

        $acc = poste('acc');
        $dat = poste('dat');
        $sql="select  kod,dat,FORMATDATE(dat,'dd.mm.yyyy') as dat1, TIME_FROM,TIME_TO, CLIENTS_CNT,
(select name from spr_sotr where kod=sotr) as sotr_name ,
(select name from spr_tov where kod=tov) as tov_name,
  COALESCE((select first 1 l.ACCOUNT  from CB_TRANING_LIST l where l.TRANING=t.kod and l.ACCOUNT=".$acc."),0)  as acc,
       (select  count(*)  from CB_TRANING_LIST l where l.TRANING=t.kod )  as cntTran 
 from CB_TRAININGS t where dat between  'today' and '".$dat."' and tov=192002 order by dat, time_from";
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }
    static function DEL_TRAN()
    {
        $acc = poste('acc');
        $traning = poste('traning');
        $sql="delete from CB_TRANING_LIST where TRANING=".$traning." and ACCOUNT=".$acc;
//s($sql);
        SystemClass::$type_sql='EXECUTE';
        $comData=fdb_list($sql,2);
//SystemClass::setComData($comData);
    }
    static function INS_TRANING_ACC()
    {
//	s('rtyyt');
        $acc = poste('acc');
        $traning = poste('traning');
        $reserv = poste('reserv');
        // s('posle');
        if ($reserv)
            $sql="insert into cb_traning_list(traning, account,TO_RESERV) VALUES (".$traning.",".$acc.",1)";
        else
            $sql="insert into cb_traning_list(traning, account) VALUES (".$traning.",".$acc.")";
        s($sql);
        $comData=fdb_query($sql);
        SystemClass::$type_sql='EXECUTE';
//SystemClass::setComData($comData);
    }
    static function GET_TRAININGONE()
    {
        $KOD = poste('KOD');
        $acc = poste('ACC');
        $sql="select kod,DAT,FORMATDATE(t.dat,'dd.mm.yyyy') as dat1,TIME_FROM,TIME_TO,CLIENTS_CNT,(select name from spr_tov where kod=tov) as tov_name,
       COALESCE((select first 1 l.ACCOUNT  from CB_TRANING_LIST l where l.TRANING=t.kod and l.ACCOUNT=".$acc."),0)  as acc,
COALESCE((select first 1 l.TO_RESERV  from CB_TRANING_LIST l where l.TRANING=t.kod and l.ACCOUNT=".$acc."),0)  as reserv
 from CB_TRAININGS t where KOD=".$KOD ;
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }
    static function GET_CRON_VIDPRAC_ACCOUNTS()
    {
        $sql="select s.kod,t.grp,name,account,
(select name from CB_ACCOUNTS where kod=account) as acc_name,
(select name from spr_podr where kod=podr) as podr_name,
cnt,summa,ost,DATE_START,FORMATDATE(DATE_START,'dd.mm.yyyy') as DATE_START1,DATE_STOP,FORMATDATE(date_stop,'dd.mm.yyyy') as DATE_STOP1,  dat from CB_SERV_SALES s, spr_tov t
where t.kod=s.TOV and  t.GRP in (372,319,373,374,192)   and s.DATE_STOP='yesterday'  and ost>0";
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }
    static function GET_HISTORY_REALIZ_3MOUNTH()
    {
        $acc = poste('acc');
        $sql = "select (select name from spr_tov where tov=kod) as name,
rc.DOP_CNT as cnt,rc.summa,hd.dat, FORMATDATE(hd.dat,'dd.mm.yyyy') as dat1 from mn_rc_tov_out rc,mn_hd_tov_out hd
 where ACCOUNT=".$acc." 
 and hd.KOD=rc.DOC   and rc.PREDOPL_CNT=0    and COALESCE(rc.BRAK,0)=0
 and hd.dat between DATEADD(month,-3,CURRENT_TIMESTAMP)  and 'today'
 order by hd.dat ";
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }
    static function GET_HISTORY_PRED_3MOUNTH()
    {

        $acc = poste('acc');
        // $phone = poste('phone');
        $sql="select DATE_START,FORMATDATE(DATE_START,'dd.mm.yyyy') as  DATE_START1,DATE_STOP,
FORMATDATE(date_stop,'dd.mm.yyyy') as  DATE_STOP1,FORMATDATE(DATE_SALE,'dd.mm.yyyy') as DAT, 
KOD, NAME_TOV as NAME, PODR_NAME, CNT, SUMMA, OST, DATE_START, DATE_STOP, DATE_SALE, TYPE_USL,ost_vidpr,
 CNT_VISIT, PACKAGE,GRP 
from ALT_HISTORY_USLUG_ABON(".$acc.") a where  DATE_SALE between DATEADD(month,-3,CURRENT_TIMESTAMP)  and 'today' order by DATE_SALE";
        /*$sql="select kod,(select name from spr_tov where tov=kod) as name,
        (select name from spr_podr where kod=podr) as podr_name,
        cnt,summa,ost,FORMATDATE(DATE_START,'dd.mm.yyyy') as DATE_START,FORMATDATE(date_stop,'dd.mm.yyyy') as DATE_STOP,FORMATDATE(dat,'dd.mm.yyyy') as dat from CB_SERV_SALES s
        where ACCOUNT=".$acc." and dat between DATEADD(month,-3,CURRENT_TIMESTAMP)  and 'today'
        ";*/
//
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }
    static function GET_HISTORY_PRED_POZAN()
    {

        $kod = poste('kod');
        $TYPE_USL = poste('TYPE_USL');
        // $phone = poste('phone');
        $sql="select DAT,FORMATDATE(dat,'dd.mm.yyyy') as dat1, TIME_OPEN, CNT from ALT_HISTORY_PRED_POZAN(".$kod.",".$TYPE_USL.") a order by 1";
//$sql="select FORMATDATE(dat,'dd.mm.yyyy') as dat,TIME_OPEN,dop_cnt as cnt from mn_rc_tov_out where SERV_LINK=".$kod." order by 1";
//
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }
    static function GET_CRON_ABONZA1DAY()
    {

        // $acc = poste('acc');
        // $phone = poste('phone');
        $sql="select a.phone,FORMATDATE(u.date_stop,'dd.mm.yyyy') as date_stop,a.kod as ACC,
(select  price from cb_pack_prices pp where pp.package=p.kod and pp.month_cnt=u.month_cnt ) as price,
g.name as grp_name,a.name as Klient,LAST_NAME,FIRST_NAME,
COALESCE(LAST_NAME,FIRST_NAME) as name from CB_USED_PACK u, CB_ACCOUNTS a,cb_packages p, cb_pack_groups g
where a.kod=u.ACCOUNT and u.DATE_STOP= cast('today' as date)+1 and u.send is null
and phone is not null and strlen(a.phone)=10  and p.kod=u.package and g.kod=p.grp
 and ( p.grp='09' or p.grp='0905' or p.grp='06' or p.grp='0906' or p.grp='0908')
order by a.name";
//
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }


    static function GET_CRON_ZAPISZADAY()
    {

        // $acc = poste('acc');
        // $phone = poste('phone');
        $sql="select C.KOD AS acc, phone,FORMATDATE(r.dat,'dd.mm.yyyy') as dat,time_start,name,(select name from spr_podr where kod=r.podr) as podr_name 
from CB_RESERVATION r,cb_accounts c where r.ACCOUNT=c.KOD and r.dat='tomorrow'
 and r.podr <>'06'
and phone is not null and r.REJECT_TYPE is null
and r.place<>'1113' and r.place<>'1111'
and strlen(phone)=10";
//
        s($sql);
        $comData=fdb_list($sql,2);
        s($comData);
        SystemClass::setComData($comData);
    }
    static function GET_PRICES()
    {
        s('$aTov');
//	s($_POST);
        //$aTov = poste('atovs');
        $aTov = poste('a_tovs');
        $a_abons = poste('a_abons');
        $aNewTovs=[];
        $aNewAbon=[];
        //	s($aTov);
        if (!empty($aTov)){
            foreach ($aTov as $item) {
                $sql="select (select price from PRC_GET_OUT_PRICE_EX('today',t.kod,'0000','0')) as PRICE_OUT
        from spr_tov t where kod='".$item['tov']."'";
                //s($sql);
                $new_price =  fdb_list($sql);
                if (!empty($new_price[0]['PRICE_OUT']))
	   {
           s('tyt1');
           $new_price = round($new_price[0]['PRICE_OUT'],2);
           s('$new_price='.$new_price[0]['PRICE_OUT']);
           $item['new_price'] = $new_price;

       }else
		   $item['new_price']=-1;


	   $aNewTovs[] = $item;
	 //  s($new_price);

    }
        }


//s($_POST);
        // $phone = poste('phone');
//$sql="select a.PACKAGE,DATE_START,FORMATDATE(DATE_START,'dd.mm.yyyy') DATE_START1,DATE_STOP,FORMATDATE(DATE_STOP,'dd.mm.yyyy') DATE_STOP1,FORMATDATE(DATE_SALE,'dd.mm.yyyy') DATE_SALE, a.MONTH_CNT,
// a.ACCOUNT, a.SUMMA, a.CNT_VISIT, a.TRANINGS_CNT, a.NAME from ALT_PACK_NOW('today','today','0',0,0) a where ACCOUNT=".$acc;

//s($sql);
//$comData=fdb_list($sql,2);
        $comData=['data',123];
        s($aNewTovs);
        SystemClass::setComData($aNewTovs);
    }
    static function GET_PACK()
    {
        $acc = poste('acc');
        // $phone = poste('phone');
//$sql="select a.PACKAGE,DATE_START,FORMATDATE(DATE_START,'dd.mm.yyyy') DATE_START1,DATE_STOP,FORMATDATE(DATE_STOP,'dd.mm.yyyy') DATE_STOP1,FORMATDATE(DATE_SALE,'dd.mm.yyyy') DATE_SALE, a.MONTH_CNT,
// a.ACCOUNT, a.SUMMA, a.CNT_VISIT, a.TRANINGS_CNT, a.NAME from ALT_PACK_NOW('today','today','0',0,0) a where ACCOUNT=".$acc;
        $sql="select a.PACKAGE,DATE_START,FORMATDATE(DATE_START,'dd.mm.yyyy') DATE_START1,DATE_STOP,FORMATDATE(DATE_STOP,'dd.mm.yyyy') DATE_STOP1,FORMATDATE(DATE_SALE,'dd.mm.yyyy') DATE_SALE,
 a.MONTH_CNT, a.ACCOUNT, a.SUMMA, a.CNT_VISIT, a.TRANINGS_CNT, a.NAME,ost,sotr_name
 from ALT_PACK_NOW('today','today','0',0,0,".$acc.")   a
 ";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }
    static function GET_ACC()
    {
        $phone = poste('phone');
        $sql="select kod as acc,name,FIRST_NAME,MIDDLE_NAME,LAST_NAME,PSW,SALE_PRC,IS_ACTIVE,CONTINGENT,PASSPORT,
PASSPORT_TYPE,ADDRESS,CARS,BIRTHDAY,(select age from prc_get_age(birthday)) as age,
INN_CODE,
 (select ft.DAT from SPR_FOOD_TYPES ft where ft.kod=a.kod) as father_datVud,
 (select ft.name from SPR_FOOD_TYPES ft where ft.kod=a.kod) as father_name,
 (select ft.PERIOD from SPR_FOOD_TYPES ft where ft.kod=a.kod) as father_typeDoc,
 (select q.name from SPR_FOOD_TYPES ft,SPR_FOOD_QUEUE q where q.KOD=ft.FOOD_QUEUE and  ft.kod=a.kod) as father_kumVud,
CARD_NUM,COALESCE(PHONE,CONTACTS) as phone,SUM_ALL_SALES,PRIM,
(case when CONTINGENT='08' then (select first 1 kod from spr_sotr where  phones =phone) else '' end) as sotr
 from CB_ACCOUNTS a where phone like '%".$phone."'";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }
    static function GET_ZAPIS()
    {
        $acc = poste('acc');
        $cnt_day = poste('cnt_day');
        // $phone = poste('phone');
        $sql="select dat as dat1,  FORMATDATE(dat,'dd.mm.yyyy') as dat, TIME_START,time_stop,
(select name from spr_podr where kod=podr) as podr_name,
(select name from spr_sotr where kod=sotr) as sotr_name  
from CB_RESERVATION where ACCOUNT=".$acc."  and  REJECT_TYPE is null and  dat between 'today' and cast('today' as date)+".$cnt_day."
and PLACE<>'1111'
 order by 1,time_start";//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }
    static function GET_ZAPIS_TRENER()
    {
        $sotr = poste('sotr');
        $cnt_day = poste('cnt_day');
        // $phone = poste('phone');
        $sql="select dat as dat1, FORMATDATE(dat,'dd.mm.yyyy') as dat, TIME_START,time_stop,
(select name from spr_podr where kod=podr) as podr_name,
(select name from CB_ACCOUNTS where kod=account) as acc_name  
from CB_RESERVATION where sotr='".$sotr."'   and  REJECT_TYPE is null  and  dat between 'today' and cast('today' as date)+".$cnt_day."
order by 1 ,time_start";//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }
    static function GET_PRED_SALE()
    {
        $acc = poste('acc');
        // $phone = poste('phone');
        $sql="select FORMATDATE(DATE_SALE,'dd.mm.yyyy') as DATE_SALE,FORMATDATE(DATE_STOP,'dd.mm.yyyy') as DATE_STOP1,DATE_STOP,FORMATDATE(DATE_START,'dd.mm.yyyy') as DATE_START1,DATE_START, SERV_NAME,OST,SOTR_NAME  from ALT_CB_PRC_RP_SERV_OST('today','0',".$acc.")";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }

    static function GET_TOVS_MENU()
    {
        $sql="select KOD,GRP,
WEIGHT, ALT_PRICE,
(select price from PRC_GET_OUT_PRICE_EX('today',t.kod,'0000','0')) as PRICE_OUT, 
case  when NOT_USE>0 or NO_FISC>0 then 1 else 0 end as not_use ,
ed,UA_NAME as NAME,DOP_INFO from spr_tov t where UA_NAME is not null 
 order by grp,ua_name";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }
    static function GET_GRP()
    {
        $sql="select kod,COALESCE(prim,name) as name from SPR_GROUP where WHOLE=1 or NO_RESERV=1";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }

    static function GET_OST_PODRS()
    {
        $sql="select KOD as podr_kod, PODR_NAME, GRP, TOV, CNT, NAME from ALT_TOV_OST_ALL('01;03') p order by tov";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }
    static function GET_OST_ALL()
    {
        $sql="select sum(cnt) AS cnt, grp,tov,name from ALT_TOV_OST_ALL('01;03') group by grp,tov,name  order by tov";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }

    static function GET_TOV_OST()
    {
        $tov = poste('tov');
        $sql="select cnt from Alt_tov_ost('today','".$tov."','0')";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }

    static function GET_ORG()
    {
        $OKPO = poste('OKPO');
        $sql="select KOD,NAME, okpo,DPA_IKOD as  num_dog,prim as RS,addr,tel as phone  from spr_org where OKPO like'%".$OKPO."'";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }

    static function GET_STATUS_ID_ZAKAZ()
    {
        $site_NUM= poste('site_NUM');
        s('$site_NUM='.$site_NUM);
        s($_POST);
        $sql="select first 1 kod,DOC_STATE,summa from mn_hd_tov_reserv where MENU_PRIM='".$site_NUM."'";
//s($sql);
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }
    static function ins_acc()
    {
        $phone = poste('phone');
        $name = poste('name');
        $E_MAIL = poste('E_MAIL');

        $sql="insert into  CB_ACCOUNTS  (name,sex,CARD_NUM,IS_ACTIVE,E_MAIL,PHONE,prim,contingent) VALUES ('".$name."',1,'SITE',1,'".$E_MAIL."','".$phone."','INTERNET',3)";
//s($sql);
        $comData=fdb_query($sql);
        SystemClass::$type_sql='EXECUTE';
//SystemClass::setComData($comData);
    }
    static function INS_ORG()
    {
        $phone = poste('phone');
        $name = poste('name');
        $OKPO = poste('OKPO');
        $num_dog = poste('num_dog');
        $RS = poste('RS');
        $addr = poste('addr');


        $sql="insert into spr_org (NAME,okpo,DPA_IKOD,prim,addr,tel,grp) values ('".$name."','".$OKPO."','".$num_dog."','".$RS."','".$addr."','".$phone."','02')";
//s($sql);
        $comData=fdb_query($sql);
        SystemClass::$type_sql='EXECUTE';
//SystemClass::setComData($comData);
    }
    static function UPD_ORDER_PAY()
    { s($_POST);
        $ORDER_ID = poste('ORDER_ID');

        $sql="EXECUTE PROCEDURE ALT_UPD_ORDER_PAY(".$ORDER_ID.")";
//s($sql);
        $comData=fdb_query($sql);

        $sql="select ID_KOD_TABLE_SALE from ALT_ZAKAZ_ONLINE where kod=".$ORDER_ID;
        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }




    static function INS_SEL_NEW_ORDER_PAY()
    { s($_POST);
        $PHONE = poste('PHONE');
        $ACC = poste('ACC');
        $SUMMA = poste('SUMMA');
        $PRED_ABON = poste('PRED_ABON');
        $TOV = poste('TOV');
        $TP = poste('TP');
        $ORDER_ID_SITE = poste('ORDER_ID_SITE');

        $sql="EXECUTE PROCEDURE ALT_NEW_ORDER_PAY(".$ACC.",'".$PHONE."',".$SUMMA.",".$PRED_ABON.",'".$TOV."','".$TP."','".$ORDER_ID_SITE."')";
//s($sql);
        $comData=fdb_query($sql);
        if ($PRED_ABON>0)
            $sql="SELECT KOD,
(select t.name from spr_tov t, CB_SERV_SALES rc where rc.kod=PRED_ABON  and t.kod=rc.TOV) as name_tov
 FROM ALT_ZAKAZ_ONLINE WHERE ".$PRED_ABON."=PRED_ABON AND ACC=".$ACC;
        else
            $sql="SELECT KOD FROM ALT_ZAKAZ_ONLINE WHERE ".$ORDER_ID_SITE."=ORDER_ID_SITE AND ACC=".$ACC;

        $comData=fdb_list($sql,2);
        SystemClass::setComData($comData);
    }

    static function INS_NEW_ZAKAZ()
    { s($_POST);
        $SITE_NUM = poste('site_NUM');
        $PRIM = poste('PRIM');
        $SALE_PRC = poste('SALE_PRC');
        $EXECUTION_DATE = poste('EXECUTION_DATE');
        $HOUSE = poste('HOUSE');
        $FLAT = poste('FLAT');
        $PHONE= poste('phone');
        $ADDRPRIM= poste('ADDRPRIM');
        $ACC= poste('aCC');
        $TYPE_OPL= poste('TYPE_OPL');
        $STREET_NAME= poste('STREET_NAME');
        $ORG= poste('ORG');
        $SHIP_TYPE= poste('SHIP_TYPE');
        $DOP_INFO= poste('DOP_INFO');


        $sql="EXECUTE PROCEDURE ALT_INS_ZAKAZ('03','".$SITE_NUM."','".$PRIM."',".$SALE_PRC.",'0','".$EXECUTION_DATE."','".$HOUSE."','".$FLAT."','".$PHONE."','".$ADDRPRIM."',".$ACC.",".$TYPE_OPL.",'".$STREET_NAME."','".$DOP_INFO."','".$ORG."',".$SHIP_TYPE.")";
//s($sql);
        $comData=fdb_query($sql);
        SystemClass::$type_sql='EXECUTE';
//SystemClass::setComData($comData);
    }
    static function ADD_TOV_ZAKAZ()
    {
        $KOD_ZAKAZ = poste('KOD_ZAKAZ');
        $tov = poste('tov');
        $cnt = poste('cnt');
        $price = poste('price');
        $summa = poste('summa');


        $sql="insert into MN_RC_TOV_RESERV (doc,tov,cnt,dop_cnt,price,dop_price,summa,ORIGIN_PRICE) values ('".$KOD_ZAKAZ."','".$tov."','".$cnt."','".$cnt."','".$price."','".$price."','".$summa."',(select price from PRC_GET_OUT_PRICE_EX('today','".$tov."','0000','0') ))";
//s($sql);
        $comData=fdb_query($sql);
        SystemClass::$type_sql='EXECUTE';
//SystemClass::setComData($comData);
    }
    static function INS_NEW_UPD_ACC()
    { s($_POST);
        $ACC = poste('ACC');
        $NUM_SITE = poste('NUM_SITE');
        $FIO = poste('FIO');
        $BIRTHDAY = poste('BIRTHDAY');
        $PHONE = poste('PHONE');
        $TYPE_DOC = poste('TYPE_DOC');
        $CER= poste('CER');
        $NUMBER= poste('NUMBER');
        $KUMVUDAN= poste('KUMVUDAN');
        $DAT_VUDACHI= poste('DAT_VUDACHI');
        $FIO_PARENT= poste('FIO_PARENT');
        $BIRTHDAY_PARENT= poste('BIRTHDAY_PARENT');
        $TYPE_DOC_PARENT= poste('TYPE_DOC_PARENT');
        $CER_PARENT= poste('CER_PARENT');
        $NUMBER_PARENT= poste('NUMBER_PARENT');
        $KUMVUDAN_PARENT= poste('KUMVUDAN_PARENT');
        $DAT_VUDACHI_PARENT= poste('DAT_VUDACHI_PARENT');
        $DAT_REGIST= poste('DAT_REGIST');

        $FIO = str_replace("'",'"',$FIO);
        $FIO = str_replace("?",'"',$FIO);
        $FIO_PARENT = str_replace("'",'"',$FIO_PARENT);
        $FIO_PARENT = str_replace("?",'"',$FIO_PARENT);
        $KUMVUDAN = str_replace("'",'"',$KUMVUDAN);
        $KUMVUDAN = str_replace("?",'"',$KUMVUDAN);
        $KUMVUDAN = substr($KUMVUDAN,0,249);
        $KUMVUDAN_PARENT = str_replace("'",'"',$KUMVUDAN_PARENT);
        $KUMVUDAN_PARENT = str_replace("?",'"',$KUMVUDAN_PARENT);
        $KUMVUDAN_PARENT = substr($KUMVUDAN_PARENT,0,249);
        $sql="EXECUTE PROCEDURE ALT_INS_UPD_ACC($ACC,$NUM_SITE,'".$FIO."','".$BIRTHDAY."','".$PHONE."',
        '".$TYPE_DOC."','".$CER."','".$NUMBER."','".$KUMVUDAN."','".$DAT_VUDACHI."','".$FIO_PARENT."',
        '".$BIRTHDAY_PARENT."','".$TYPE_DOC_PARENT."','".$CER_PARENT."','".$NUMBER_PARENT."',
        '".$KUMVUDAN_PARENT."', '".$DAT_VUDACHI_PARENT."','".$DAT_REGIST."')";
//s($sql);
        $comData=fdb_query($sql);
        SystemClass::$type_sql='EXECUTE';
//SystemClass::setComData($comData);
    }
}
?>