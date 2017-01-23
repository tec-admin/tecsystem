INSERT INTO tecdb.m_members (	id ,
								insrtflg,
								deletflg,
								name_kana,
								sex,
								email,
								student_id,
								staff_no,
								name_jp,
								student_id_jp,
								original_user_flg,
								lastupdate,
								lastupdater
							) VALUES (	'admin',
										1,
										0,
										'ƒJƒ“ƒŠƒVƒƒ',
										1,
										'',
										'',
										'',
										'ŠÇ—Ò',
										'',
										0,
										'2016/08/29 10:10:10',
										'admin'
									);


INSERT INTO public.t_member_attribute (	id ,
										password,
										roles,
										display_flg,
										lastupdate,
										lastupdater
									) VALUES (	'admin',
												'21232f297a57a5a743894a0e4a801fc3',
												'Administrator',
												1,
										'2016/08/29 10:10:10',
										'admin'
											);



