
-- キャンパスマスタ
create table public.m_campuses (
  id bigint not null
  , campus_name text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , display_flg integer default 1 not null
  , order_num integer
  , primary key (id)
) ;

-- 文書種別マスタ
create table public.m_dockinds (
  id bigint not null
  , document_category text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , display_flg integer default 1 not null
  , order_num integer
  , primary key (id)
) ;

-- 学部マスタ
create table public.m_faculties (
  id integer not null
  , name text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , display_flg integer default 1 not null
  , order_num integer
  , primary key (id)
) ;

-- public.m_jyugyo_timetables
create table public.m_jyugyo_timetables (
  id integer not null
  , starttime time without time zone not null
  , endtime time without time zone not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , display_flg integer default 1 not null
  , order_num integer
  , primary key (id)
) ;

-- m_lclasses
create table public.m_lclasses (
  id bigint not null
  , title text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- m_library_ranges
create table public.m_library_ranges (
  id integer not null
  , title text not null
  , roles text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 権限マスタ
create table public.m_permissions (
  id integer not null
  , m_member_roles text not null
  , roles_jp text not null
  , roles_jp_clipped_form text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , display_flg integer default 1 not null
  , order_num integer
  , search_flg integer default 1 not null
  , primary key (id)
) ;

-- 相談場所マスタ
create table public.m_places (
  id bigint not null
  , consul_place text not null
  , m_campus_id bigint default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , display_flg integer default 1 not null
  , order_num integer
  , primary key (id)
) ;

-- m_ranges
create table public.m_ranges (
  id integer not null
  , title text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 設定マスタ
create table public.m_settings (
  id bigint not null
  , name text not null
  , content text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , display_flg integer default 1 not null
  , primary key (id)
) ;

alter table public.m_settings add constraint m_settings_name_key
  unique (name) ;

-- シフトマスタ
create table public.m_shifts (
  id bigint not null
  , m_term_id bigint not null
  , m_dockind_id bigint default 0 not null
  , m_place_id bigint default 0 not null
  , dayno integer default 0 not null
  , starttime time without time zone
  , endtime time without time zone
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 学期マスタ
create table public.m_terms (
  id bigint not null
  , name text not null
  , startdate date not null
  , enddate date not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , year text not null
  , shift_startdate date
  , shift_enddate date
  , primary key (id)
) ;

-- 時限時間マスタ
create table public.m_timetables (
  id integer not null
  , starttime time without time zone not null
  , endtime time without time zone not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , display_flg integer default 1 not null
  , order_num integer
  , primary key (id)
) ;

-- t_batch_log
create table public.t_batch_log (
  id integer not null
  , name text not null
  , start_date timestamp without time zone not null
  , end_date timestamp without time zone
  , specified_date text not null
  , status text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_communities
create table public.t_communities (
  id integer not null
  , m_member_id_creator text not null
  , t_course_id bigint default 0 not null
  , name text not null
  , t_file_id bigint default 0 not null
  , summary text not null
  , closedate timestamp without time zone not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , t_group_id bigint default 0 not null
  , primary key (id)
) ;

-- t_community_topics
create table public.t_community_topics (
  id integer not null
  , t_community_id integer default 0 not null
  , m_member_id_creator text not null
  , title text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_course_reports
create table public.t_course_reports (
  id integer not null
  , title text not null
  , startaccept timestamp without time zone not null
  , endaccept timestamp without time zone not null
  , t_file_id bigint default 0 not null
  , question text not null
  , autoscoringcomment text not null
  , autoscoring integer default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , t_course_id bigint default 0 not null
  , status integer default 0 not null
  , primary key (id)
) ;

-- t_courses
create table public.t_courses (
  id integer not null
  , title text not null
  , startdate timestamp without time zone not null
  , enddate timestamp without time zone not null
  , sort_no bigint not null
  , datalink_course_id bigint not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- ファイル
create table public.t_files (
  id integer not null
  , data bytea not null
  , name text not null
  , type text not null
  , filesize integer default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_group_files
create table public.t_group_files (
  id integer not null
  , t_group_id integer default 0
  , t_file_id bigint default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_groups
create table public.t_groups (
  id integer not null
  , t_course_id integer default 0 not null
  , groupname text not null
  , enddate timestamp without time zone not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- お知らせ添付ファイル
create table public.t_infomation_files (
  id bigint not null
  , t_infomation_id integer default 0 not null
  , t_file_id bigint not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- お知らせ
create table public.t_infomations (
  id integer not null
  , title text not null
  , body text not null
  , m_range_id bigint default 0 not null
  , m_member_id_from text not null
  , m_member_id_to text not null
  , t_course_id bigint default 0 not null
  , subtitle text
  , startdate timestamp without time zone
  , enddate timestamp without time zone
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , calendar_flag text default '0' not null
  , allday_flag text default '0' not null
  , primary key (id)
) ;

-- 時間割担当テーブル
create table public.t_jikanwari_kyoin (
  jyu_nendo character varying(8) not null
  , jyu_knr_no character varying(12) not null
  , kyoincd character varying(10) not null
  , sekiji character varying(4)
  , jikan character varying(6)
  , yobi_jigen character varying(40)
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (jyu_nendo,jyu_knr_no,kyoincd)
) ;

-- 指導内容
create table public.t_leadings (
  id integer not null
  , t_reserve_id character varying default 0 not null
  , m_dockind_id bigint default 0 not null
  , m_subject_id bigint default 0 not null
  , submitdate date not null
  , progress bigint default 0 not null
  , m_member_id_charge text default '0' not null
  , counsel text not null
  , teaching text not null
  , remark text not null
  , summary text not null
  , leading_comment text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , f_cancel text
  , cancel_flag text default '0'
  , primary key (id)
) ;

-- public.t_leadings_tmp
create table public.t_leadings_tmp (
  id integer not null
  , t_reserve_id character varying default 0 not null
  , m_dockind_id bigint default 0 not null
  , m_subject_id bigint default 0 not null
  , submitdate date not null
  , progress bigint default 0 not null
  , m_member_id_charge text default '0' not null
  , counsel text not null
  , teaching text not null
  , remark text not null
  , summary text not null
  , leading_comment text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , f_cancel text
  , cancel_flag text default '0'
  , primary key (id)
) ;

-- ユーザー属性テーブル
create table public.t_member_attribute (
  id character varying(8) not null
  , password text
  , roles text not null
  , languages integer default 0 not null
  , display_flg integer default 0 not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_communities
create table public.t_member_communities (
  id integer not null
  , m_member_id text not null
  , t_community_id integer default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_community_comments
create table public.t_member_community_comments (
  id integer not null
  , m_member_id text not null
  , t_community_topic_id integer not null
  , commentbody text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_courses
create table public.t_member_courses (
  id integer not null
  , t_course_id integer default 0 not null
  , m_member_id text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_groups
create table public.t_member_groups (
  id integer not null
  , m_member_id text not null
  , t_group_id integer default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_leadmemos
create table public.t_member_leadmemos (
  id integer not null
  , m_member_id_from text not null
  , m_member_id_to text not null
  , title text not null
  , body text not null
  , t_file_id bigint default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_libraries
create table public.t_member_libraries (
  id integer not null
  , m_member_id text not null
  , title text not null
  , t_file_id bigint default 0 not null
  , summary text not null
  , m_library_range_id integer default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_library_items
create table public.t_member_library_items (
  id integer not null
  , t_member_library_id integer default 0 not null
  , t_member_report_id integer default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_portfolio_tags
create table public.t_member_portfolio_tags (
  id integer not null
  , t_member_report_id integer not null
  , t_portfolio_tag_id integer default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- m_members に関連付いたユーザープロフィール
create table public.t_member_profiles (
  id integer not null
  , m_member_id text not null
  , t_file_id bigint default 0 not null
  , email text not null
  , selfintroduction text
  , hobby text
  , etc text
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_report_rates
create table public.t_member_report_rates (
  id integer not null
  , t_member_report_id integer default 0 not null
  , m_member_id_from text not null
  , rate text not null
  , status integer not null
  , feedback text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_member_reports
create table public.t_member_reports (
  id integer not null
  , m_member_id text not null
  , t_course_id bigint not null
  , t_course_report_id bigint default 0 not null
  , title text not null
  , answer text not null
  , t_file_id bigint default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_portfolio_tags
create table public.t_portfolio_tags (
  id integer not null
  , text text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- t_preview_course_reports
create table public.t_preview_course_reports (
  id integer not null
  , m_member_id text not null
  , t_course_id bigint not null
  , title text not null
  , startaccept timestamp without time zone not null
  , endaccept timestamp without time zone not null
  , question text not null
  , autoscoringcomment text not null
  , autoscoring integer default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- リマインダーメール
create table public.t_reminders (
  id integer not null
  , t_reserve_id character varying default 0 not null
  , m_member_id_from text default '0' not null
  , m_member_id_to text default '0' not null
  , senddatetime timestamp without time zone not null
  , subject text not null
  , body text not null
  , status bigint default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 予約コメント添付ファイル
create table public.t_reserve_comment_files (
  id integer not null
  , t_reserve_comment_id bigint default 0 not null
  , t_work_file_id bigint default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 予約コメント
create table public.t_reserve_comments (
  id integer not null
  , t_reserve_id character varying not null
  , reservecomment text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 予約添付ファイル
create table public.t_reserve_files (
  id integer not null
  , t_reserve_id character varying default 0 not null
  , t_work_file_id bigint default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- public.t_reserve_history
create table public.t_reserve_history (
  id integer not null
  , t_reserve_id character varying not null
  , m_member_id_reserver text default '0' not null
  , reservationdate date not null
  , m_shift_id bigint default 0 not null
  , m_subject_id character varying(12) default 0
  , submitdate date
  , progress bigint default 0
  , question text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , run_reserve text default '0'
  , historyclass text default '0'
  , delete_flag text default '0'
  , primary key (id)
) ;

-- シフト担当者
create table public.t_shiftcharges (
  id integer not null
  , reservationdate date not null
  , m_shift_id bigint default 0 not null
  , t_member_id_charge bigint not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 日付/時間帯別スタッフシフト
create table public.t_shiftdetails (
  id integer not null
  , m_member_id text default '0' not null
  , shiftdate date not null
  , m_shift_id bigint default 0 not null
  , dow bigint default 0 not null
  , type text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- シフト受入数
create table public.t_shiftlimits (
  id integer not null
  , reservationdate date not null
  , m_shift_id bigint default 0 not null
  , reservelimit bigint default 0 not null
  , limitname text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- スタッフシフト
create table public.t_staffshifts (
  id integer not null
  , m_member_id text default '0' not null
  , m_shift_id bigint default 0 not null
  , dow bigint default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- メンバーワークファイル
create table public.t_work_files (
  id integer not null
  , m_member_id text not null
  , t_file_id bigint default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;


-- 学科マスタテーブル
create table tecdb.m_gakka (
  bu character varying(4)
  , gakubu character varying(8)
  , gakka character varying(8)
  , gakka_mei character varying(240)
  , gakka_ryaku character varying(120)
  , yoto_kbn character varying(2)
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
) ;

-- 学部マスタテーブル
create table tecdb.m_gakubu (
  bu character varying(4)
  , gakubu character varying(8)
  , gakubu_mei character varying(240)
  , gakubu_ryaku character varying(120)
  , yoto_kbn character varying(2)
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
) ;

-- ユーザーマスタ
create table tecdb.m_members (
  id character varying(8) not null
  , usr_kn character varying(1)
  , taisyokn character varying(2)
  , taidtlkn character varying(4)
  , name_kana character varying(80)
  , name character varying(32)
  , sex character varying(1)
  , birthday character varying(16)
  , email character varying(100)
  , student_id character varying(12)
  , setti_cd character varying(4)
  , bukatecd character varying(2)
  , gakubucd character varying(10)
  , gakka_cd character varying(4)
  , coursecd character varying(4)
  , entrance_year integer
  , staff_no character varying(8)
  , syozokcd character varying(7)
  , sikakucd character varying(4)
  , yaksykcd character varying(5)
  , syksyucd character varying(2)
  , zaisyokn character varying(1)
  , syzkcd_c character varying(120)
  , insrtflg character varying(1) not null
  , deletflg character varying(1) not null
  , name_jp character varying(64)
  , student_id_jp character varying(26)
  , zaisekkn character varying(2)
  , nyugk_dy character varying(16)
  , sotugydy character varying(16)
  , syozkcd1 character varying(20)
  , syozkcd2 character varying(8)
  , gaknenkn character varying(2)
  , age integer default 0
  , original_user_flg integer not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 年度マスタ
create table tecdb.m_nendo (
  id integer not null
  , current_nendo character varying(4) not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 授業科目マスタ(授業明細)
create table tecdb.m_subjects (
  jyu_nendo character varying(8) not null
  , jyu_knr_no character varying(12) not null
  , jyu_knr_no_sub character varying(12) not null
  , kyoincd character varying(10) not null
  , grp4_key character varying(8)
  , gakka character varying(6)
  , kmkcd character varying(16)
  , kmkcd5 character varying(18)
  , class_subject character varying(400)
  , setti_cd character varying(4)
  , jyu_kbn character varying(4)
  , jkeitai character varying(2)
  , tan_gakki character varying(2)
  , class character varying(6)
  , yobi character varying(2)
  , jigen1 character varying(2)
  , yogen character varying(200)
  , jwaricd character varying(10)
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (jyu_nendo,jyu_knr_no,jyu_knr_no_sub,kyoincd)
) ;

-- 閉室設定テーブル
create table tecdb.t_closuredates (
  id integer not null
  , closuredate date not null
  , m_place_id integer not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 授業科目リスト
create table tecdb.t_course_list (
  jyu_nendo character varying(8) not null
  , jyu_knr_no character varying(12) not null
  , jyu_knr_no_sub character varying(12) not null
  , kyoincd character varying(10) not null
  , gakka character varying(6)
  , kmkcd character varying(16)
  , kmkcd5 character varying(18)
  , class_subject character varying(400)
  , setti_cd character varying(4)
  , jkeitai character varying(2)
  , tan_gakki character varying(2)
  , class character varying(6)
  , yobi character varying(2)
  , jigen1 character varying(2)
  , yogen character varying(200)
  , jwaricd character varying(10)
  , sekiji_top character varying(4)
  , sekiji_top_kyoinmei character varying(30)
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (jyu_nendo,jyu_knr_no,jyu_knr_no_sub,kyoincd)
) ;

-- 履修者名簿テーブル
create table tecdb.t_course_roster (
  gakse_id character varying(12) not null
  , gaksekno character varying(20) not null
  , risyunen character varying(4) not null
  , semekikn character varying(2) not null
  , kougicd character varying(20) not null
  , jyuknrno character varying(12) not null
  , risystkn character varying(2) not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (gakse_id,risyunen,semekikn,kougicd)
) ;

-- 時間割担当テーブル
create table tecdb.t_jikanwari_kyoin (
  jyu_nendo character varying(8) not null
  , jyu_knr_no character varying(12) not null
  , kyoincd character varying(10) not null
  , sekiji character varying(4)
  , tanuke character varying(2)
  , jikan character varying(6)
  , yobi_jigen character varying(40)
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (jyu_nendo,jyu_knr_no,kyoincd)
) ;

-- 授業・時間割日程_テーブル
create table tecdb.t_jyugyo_jikanwari (
  jyu_nendo character varying(8) not null
  , jyu_knr_no character varying(12) not null
  , jwari_seq character varying(4) not null
  , riyou_kn character varying(6) not null
  , jyugyo_seq character varying(4) not null
  , sykjyudy character varying(16)
  , sykjigen character varying(2)
  , sykjzgkn character varying(2)
  , jyugyody character varying(16)
  , jigen character varying(2)
  , jzengokn character varying(2)
  , jikoku_f character varying(8)
  , jikoku_t character varying(8)
  , display_flg integer default 0 not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (jyu_nendo,jyu_knr_no,jwari_seq,riyou_kn,jyugyo_seq)
) ;

-- 授業管理テーブル
create table tecdb.t_jyugyo_kanri (
  jyu_nendo character varying(8) not null
  , jyu_knr_no character varying(12) not null
  , sekiji_top character varying(4)
  , sekiji_top_kyoinmei character varying(30) not null
  , tanto_other_falg character varying(1) default '0' not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (jyu_nendo,jyu_knr_no)
) ;

-- 教職員５桁８桁マッピングテーブル
create table tecdb.t_kyoin8_5_m (
  ky_kyoincd character varying(10)
  , ky_jkyoincd8 character varying(16)
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
) ;

-- 指導内容テーブル
create table tecdb.t_leadings (
  id integer not null
  , t_reserve_id character varying default 0 not null
  , submitdate date not null
  , m_member_id_charge text default '0' not null
  , staff_no character varying(8)
  , name_jp character varying(64)
  , counsel text
  , teaching text
  , remark text
  , summary text
  , leading_comment text
  , submit_flag text default '0'
  , cancel_flag text default '0'
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 指導内容
create table tecdb.t_leadings_tmp (
  id integer not null
  , t_reserve_id character varying default 0 not null
  , submitdate date not null
  , m_member_id_charge text default '0' not null
  , staff_no character varying(8)
  , name_jp character varying(64)
  , counsel text
  , teaching text
  , remark text
  , summary text
  , leading_comment text
  , submit_flag text default '0'
  , cancel_flag text default '0'
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- tecdb.t_member_attribute
create table tecdb.t_member_attribute (
  id character varying(8) not null
  , password text not null
  , roles text not null
  , languages integer default 0 not null
  , display_flg integer default 0 not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- tecdb.t_reserve_history
create table tecdb.t_reserve_history (
  id integer not null
  , t_reserve_id character varying not null
  , m_member_id_reserver text default '0' not null
  , student_id character varying(12)
  , name_jp character varying(64)
  , email character varying(100)
  , sex character varying(1)
  , setti_cd character varying(4)
  , syozkcd1 character varying(20)
  , syozkcd2 character varying(8)
  , entrance_year integer
  , gaknenkn character varying(2)
  , reservationdate date not null
  , nendo character varying(4)
  , m_shift_id bigint default 0 not null
  , jwaricd character varying(10)
  , class_subject character varying(400)
  , sekiji_top_kyoinmei character varying(30)
  , submitdate date
  , progress bigint default 0
  , question text not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , run_reserve text default '0'
  , historyclass text default '0'
  , delete_flag text default '0'
  , primary key (id)
) ;

-- 予約テーブル
create table tecdb.t_reserves (
  id character varying not null
  , m_member_id_reserver character varying(8) default '0' not null
  , student_id character varying(12)
  , name_jp character varying(64)
  , email character varying(100)
  , sex character varying(1)
  , setti_cd character varying(4)
  , syozkcd1 character varying(20)
  , syozkcd2 character varying(8)
  , entrance_year integer
  , gaknenkn character varying(2)
  , reservationdate date not null
  , nendo character varying(4)
  , m_shift_id bigint default 0 not null
  , jwaricd character varying(10)
  , class_subject character varying(400)
  , sekiji_top_kyoinmei character varying(30)
  , submitdate date
  , progress bigint default 0
  , question text not null
  , run_reserve text default '0' not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 所属１テーブル
create table tecdb.t_syozoku1 (
  setti_cd character varying(8) not null
  , syozkcd1 character varying(20) not null
  , szknam_c character varying(240) not null
  , szknam_r character varying(120) not null
  , szknam_k character varying(480)
  , szknam_e character varying(360)
  , validy_f integer
  , validy_t integer
  , gakseflg character varying(2)
  , kssyoflg character varying(2)
  , sort_no integer not null
  , z008syszkcd1 character varying(8) not null
  , z008syunam_c character varying(240) not null
  , z008syunam_r character varying(120) not null
  , z008syunam_k character varying(480)
  , z008syunam_e character varying(360)
  , z008szsrt_no integer not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (setti_cd,syozkcd1)
) ;

-- 所属２テーブル
create table tecdb.t_syozoku2 (
  setti_cd character varying(8) not null
  , syozkcd1 character varying(20) not null
  , syozkcd2 character varying(8) not null
  , szknam_c character varying(240) not null
  , szknam_r character varying(120) not null
  , validy_f integer
  , validy_t integer
  , gakseflg character varying(2)
  , kssyoflg character varying(2)
  , sort_no integer not null
  , z008syszkcd1 character varying(8) not null
  , z008syunam_c character varying(240) not null
  , z008syunam_r character varying(120) not null
  , z008szsrt_no integer not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (setti_cd,syozkcd1,syozkcd2)
) ;


-- Myテーマ・学内施設マスタ
create table tecfolio.m_mythemes (
  id character varying not null
  , m_member_id character varying(8) default '0' not null
  , name_jp character varying not null
  , syzkcd_c character varying not null
  , name character varying(255) not null
  , disabled_flag character varying(1) default '0' not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , order_num integer
  , primary key (id)
) ;

-- ルーブリックマスタ
create table tecfolio.m_rubric (
  id character varying not null
  , original_id character varying
  , name character varying not null
  , theme character varying
  , memo character varying
  , original_name_jp character varying
  , editor_name_jp character varying
  , t_rubric_license_id integer
  , published_flag character varying(1) default '0' not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 登録授業科目マスタ
create table tecfolio.m_subjects_registered (
  id character varying not null
  , jyu_nendo character varying(8) not null
  , jyu_knr_no character varying(12) not null
  , jyu_knr_no_sub character varying(12) not null
  , kyoincd character varying(10) not null
  , jkyoincd8 character varying(16) not null
  , grp4_key character varying(8)
  , gakka character varying(6)
  , kmkcd character varying(16)
  , kmkcd5 character varying(18)
  , class_subject character varying(400)
  , setti_cd character varying(4)
  , jyu_kbn character varying(4)
  , jkeitai character varying(2)
  , tan_gakki character varying(2)
  , class character varying(6)
  , yobi character varying(2)
  , jigen1 character varying(2)
  , yogen character varying(200)
  , jwaricd character varying(10)
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , publicity integer default 1 not null
  , primary key (id)
) ;

-- 相談内容(メンター)テーブル
create table tecfolio.t_chat_mentor (
  id integer not null
  , m_mytheme_id character varying not null
  , t_mentor_id character varying not null
  , m_member_id character varying not null
  , tgt_member_id character varying
  , title text
  , body text
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 相談内容(授業科目)テーブル
create table tecfolio.t_chat_subject (
  id integer not null
  , m_subject_reg_id character varying not null
  , m_member_id character varying not null
  , m_member_name_jp character varying not null
  , title text
  , body text
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 相談内容(授業科目)・コンテンツ間マッピングテーブル
create table tecfolio.t_chat_subject_contents (
  id character varying not null
  , t_chat_subject_id integer not null
  , t_content_id character varying not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 添付ファイル実体テーブル
create table tecfolio.t_content_files (
  id integer not null
  , data bytea not null
  , name text not null
  , type text not null
  , filesize integer default 0 not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- コンテンツテーブル
create table tecfolio.t_contents (
  id character varying not null
  , m_mytheme_id character varying not null
  , t_content_file_id integer
  , ref_title character varying
  , ref_url character varying
  , ref_class character varying(1)
  , poster_name character varying
  , delete_flag character varying(1) default '0'
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , publicity integer default 1 not null
  , primary key (id)
) ;

-- 登録履修者名簿テーブル
create table tecfolio.t_course_roster_registered (
  m_member_id character varying(8)
  , gakse_id character varying(12) not null
  , gaksekno character varying(20) not null
  , student_id_jp character varying(26)
  , name_jp character varying(64) not null
  , name_kana character varying(80)
  , syzkcd_c character varying(120)
  , risyunen character varying(4) not null
  , semekikn character varying(2) not null
  , kougicd character varying(20) not null
  , jyuknrno character varying(12) not null
  , risystkn character varying(2) not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (gakse_id,risyunen,semekikn,kougicd)
) ;

-- 登録授業管理テーブル
create table tecfolio.t_jyugyo_jikanwari_registered (
  jyu_nendo character varying(8) not null
  , jyu_knr_no character varying(12) not null
  , jwari_seq character varying(4) not null
  , riyou_kn character varying(6) not null
  , jyugyo_seq character varying(4) not null
  , sykjyudy character varying(16)
  , sykjigen character varying(2)
  , sykjzgkn character varying(2)
  , jyugyody character varying(16)
  , jigen character varying(2)
  , jzengokn character varying(2)
  , jikoku_f character varying(8)
  , jikoku_t character varying(8)
  , display_flg integer default 0 not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , title character varying
  , memo character varying
  , primary key (jyu_nendo,jyu_knr_no,jwari_seq,riyou_kn,jyugyo_seq)
) ;

-- メンターテーブル
create table tecfolio.t_mentors (
  id character varying not null
  , m_mytheme_id character varying not null
  , mentor_number integer default 1 not null
  , m_member_id character varying not null
  , name_jp character varying not null
  , syzkcd_c character varying not null
  , agreement_flag character varying(1) default '0' not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- ポートフォリオテーブル
create table tecfolio.t_portfolio (
  id character varying not null
  , m_mytheme_id character varying not null
  , title character varying(255) not null
  , m_rubric_id character varying
  , self_comment text
  , mentor_comment text
  , showcase_flag character varying(1) default '0'
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- ポートフォリオ・コンテンツ間マッピングテーブル
create table tecfolio.t_portfolio_contents (
  id character varying not null
  , t_portfolio_id character varying not null
  , t_content_id character varying not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- 個人情報設定テーブル
create table tecfolio.t_profiles (
  m_member_id character varying(8) not null
  , nickname character varying(100)
  , languages integer
  , email_2 character varying(100)
  , email_3 character varying(100)
  , input_name character varying(255)
  , image_name character varying(255)
  , speciality character varying(255)
  , seminar character varying(255)
  , highschool character varying(255)
  , birthday character varying(255)
  , sex character varying(255)
  , birthplace character varying(255)
  , mentor_flag character varying(1) default '0' not null
  , hobby character varying(255)
  , ability character varying(255)
  , likes text
  , dislikes text
  , personality text
  , strength character varying(255)
  , weekness character varying(255)
  , cert_1 character varying(255)
  , cert_2 character varying(255)
  , cert_3 character varying(255)
  , cert_4 character varying(255)
  , cert_5 character varying(255)
  , pr text
  , memories text
  , tried text
  , succeeded text
  , failed text
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (m_member_id)
) ;

-- 自己評価テーブル
create table tecfolio.t_rubric_input (
  id integer not null
  , t_portfolio_id character varying not null
  , vertical integer not null
  , rank integer not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- ライセンステーブル
create table tecfolio.t_rubric_license (
  id integer not null
  , name character varying not null
  , export_flag character varying(1) default '0' not null
  , secondary_use character varying
  , primary key (id)
) ;

-- ルーブリック・テーマ間マッピングテーブル
create table tecfolio.t_rubric_map (
  id integer not null
  , parent_id character varying not null
  , m_rubric_id character varying not null
  , original_flag character varying default '1' not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- ルーブリック詳細テーブル
create table tecfolio.t_rubric_matrix (
  id integer not null
  , m_rubric_id character varying not null
  , vertical integer not null
  , horizontal integer not null
  , rank integer
  , description character varying(255)
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;

-- メンター評価テーブル
create table tecfolio.t_rubric_mentor (
  id integer not null
  , t_portfolio_id character varying not null
  , vertical integer not null
  , rank integer not null
  , createdate timestamp without time zone not null
  , creator text not null
  , lastupdate timestamp without time zone not null
  , lastupdater text not null
  , primary key (id)
) ;


