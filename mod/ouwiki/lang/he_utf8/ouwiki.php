<?php
$string['modulename'] = 'ויקי (מבוזר, אישי)';
$string['modulenameplural'] = 'ויקי';

$string['subwikis'] = 'תת ויקי';
$string['subwikis_single'] = 'ויקי אחד לכולם';
$string['subwikis_groups'] = 'ויקי לכל קבוצה';
$string['subwikis_individual'] = 'ויקי לכל תלמיד';

$string['timeout']='זמן מורשה לעריכה';
$string['timeout_none']='אין מגבלת זמן';

$string['editbegin']='אפשר עריכה מ-';
$string['editend']='מנע עריכה מ-';

$string['wouldyouliketocreate']='האם אתם מעונינים ליצור אותו?';
$string['pagedoesnotexist']='דף ויקי זה אינו קיים עדיין.';
$string['startpagedoesnotexist']='הדף הראשי של ויקי זה טרם נוצר.';
$string['createpage']='יצירת דף חדש';

$string['recentchanges']='עריכות אחרונות';
$string['seedetails']='היסטורית עריכות מלאה';
$string['startpage']='דף ראשי';

$string['tab_view']='מצב תצוגה';
$string['tab_edit']='עריכת דף';
$string['tab_discuss']='דיון';
$string['tab_history']='היסטוריה';

$string['preview']='תצוגה מקדימה';
$string['previewwarning']='התצוגה הבאה של השינויים שלך טרם נשמרה.
<strong>אם השינויים לא ישמרו, העבודה תאבד.</strong> יש לשמור בעזרת המקש שבתחתית הדף.';

$string['wikifor']='צפייה בויקי לצורך: ';
$string['changebutton']='שינוי';

$string['advice_edit']='
<p>ערכו דף זה.</p>
<ul>
<li>יצירת קישור לדף על ידי שימוש בזוג סוגריים מרובעים: [[שם הדף החדש]]. הקישור לדף יהפוך זמין כאשר תשמרו את הדף הנוכחי.</li>
<li>ליצירת דף חדש, ראשית עליכם ליצר קישור באופן זה. $a</li>
</ul>
</p>
';

$string['pagelockedtitle']='דף זה נמצא בעריכה ע"\י מישהו אחר.';
$string['pagelockeddetails']='{$a->name} התחיל/ה לערוך דף זה ב {$a->lockedat}, כהמשך מ {$a->seenat}. אין אפשרות לערוך עד שהם יסיימו. ';
$string['pagelockeddetailsnojs']='{$a->name} התחילו לערוך דף זה ב {$a->lockedat}. יש להם זמן עד ה- {$a->nojs} לערוך. אין אפשרות לערוך עד שהם יסיימו';
$string['pagelockedtimeout']='זמן משבצת העריכה שלהם נגמר ב $a.';
$string['pagelockedoverride']='ברשותך גישה מיוחדת לביטול העריכה שלהם וביטול נעית הדף. אם תעשה זאת, כל מה שהם עשו ימחק! אנא חשוב בזהירות לפני הקשה
על כפתור הדריסה.';
$string['tryagain']='נסה שוב';
$string['overridelock']='מצב דריסה נעול';

$string['savefailtitle']='דף לא יכול להישמר';
$string['savefaillocked']='בזמן שערכת את הדף, מישהו נעל אותו (יכול לקרות עקב מספר מצבים כמו שימוש שלך בדפדפן חריג או שה- JavaScript שלך כבוי). לצערנו, השינויים שלך אינם יכולים להישמר כרגע.';
$string['savefaildesynch']='בזמן שערכת את הדף, מישהו אחר גם כן ביצע עריכה והוא הספיק לבצע שינוי (יכול לקרות עקב מספר מצבים כמו שימוש שלך בדפדפן חריג או שה- JavaScript שלך כבוי). לצערנו, השינויים שלך אינם יכולים להישמר היות והם ימחקו את מה שאותו משתמש כתב.';
$string['savefailcontent']='הגירסה של דף זה רשומה למטה כך שתמיד תוכלו להעתיק ולהדביק קטעים רלוונטים לתוכנה אחרת. אם ברצונכם להכניס את השינויים שלכם בחזרה לתוך הויקי בהמשך, עליכם להיזהר לא למחוק עבודה שלמישהו אחר.';
$string['returntoview']='תצוגת דף נוכחי';

$string['lockcancelled'] = 'משתמש אחר עקף את נעילת העריכה שלך והוא כרגע עורך את הדף הזה. אם ברצונך לשמור את השינויים שביצעת, יש לבחור ולהעתיק אותם לפני לחיצה על ביטול. אח\"כ יש לנסות ולערוך שוב';
$string['nojsbrowser'] = 'אנו מתנצלים, אך אתם משתמשים בדפדפן שאנו לא תומכים בו באופן מלא';
$string['nojsdisabled'] = 'ביטלתם JavaScript בהגדרות של הדפדפן שלכם';
$string['nojswarning'] = 'כתוצאה מכך, אנו יכולים להחזיק את הדף למענך רק למשך $a->דקות. יש לוודא שמירת שינויים עד  $a->deadline (כרגע: $a->now). אחרת, מישהו אחר עלול לערוך את הדף והשינויים שלך יאבדו.';

$string['countdowntext'] = 'וויקי זה מאפשר רק $a דקות לעריכה. בצעו את השינויים שלכם ולחצו שמירה או בטלו לפני שהזמן הנותר (מימין) יגיע לאפס.';
$string['countdownurgent'] = 'אנא סיימו או בטלו את העריכה שלכם כעת. אם לא תשמרו לפני שהזמן יסתיים, המערכת תבצע שמירה אוטומטית למה שכתבתם עד כה.';


$string['advice_history']='<p>הטבלה שלמטה מציגה את כל השינויים של <a href=\"$a\">הדף הנוכחי</a>.</p>
<p>אתם יכולים לראות גרסאות ישנות או לראות מה השתנה בגירסה ספציפית. אם ברצונכם להשוות שתי גירסאות כלשהן, בחרו את ה CheckBox הרלוונטי ולחצו \'השוואת מסומנים\'.</p>';

$string['changedby']='עודכן על ידי';
$string['compare']='השוואה';
$string['compareselected']='השוואת מסומנים';
$string['changes']='שינוים';
$string['actionheading']='פעולות';

$string['mustspecify2']='עליכם להגדיר בדיוק 2 גרסאות לצורך ביצוע השוואה';

$string['oldversion']='גירסה ישנה';
$string['previousversion']='הקודם: $a';
$string['nextversion']='הבא: $a';
$string['currentversion']='גירסה נוכחית';
$string['savedby']='נשמר ע\"י $a';
$string['system']='המערכת';
$string['advice_viewold']='אתם צופים בגירסה ישנה של דף זה.';

$string['index']='תוכן ענינים';
$string['tab_index_alpha']='סדר אלף בית';
$string['tab_index_tree']='מבנה היררכי';

$string['lastchange']='שינוי אחרון: {$a->date} / {$a->userlink}';
$string['orphanpages']='דפים לא מקושרים';

$string['missingpages']='דפים חסרים';
$string['advice_missingpages']='These pages are linked to, but have not yet been created.';
$string['advice_missingpage']='This page is linked to, but has not yet been created.';
$string['frompage']='מ- $a';
$string['frompages']='מ- $a...';

$string['changesnav']='שינוים';
$string['advice_diff']='הגירסה הקודמת מוצגת בצד שמאל<span class=\'accesshide\'> תחת הכותרת גירסה ישנה יותר</span>, איפה שטקסט שנימחק הינו מודגש. טקסט שנוסף מצויין בגירסה החדשה מצד ימין .<span class=\'accesshide\'> תחת הכותרת גרסה חדשה יותר</span>.<span class=\'accesshide\'> כל שינוי מצויין ע\"י זוג תמונות, אחת לפני הטקסט להוספה/ מחיקה, ואחת אחריו, עם טקסט חלופי ראוי.</span>';
$string['diff_nochanges']='העריכה לא יצרה שינויים במלל, ולכן לא נוצרו הדגשות למטה. יכול להיות שיהיו שינויים שניראים לעין.';
$string['returntohistory']='(<a href=\'$a\'>חזרה להיסטוריה</a>.)';
$string['addedbegins']='[מלל נוסף בהמשך]';
$string['addedends']='[סוף המלל הנוסף]';
$string['deletedbegins']='[קטע מחוק בהמשך]';
$string['deletedends']='[סוף קטע מחוק]';


$string['ouwiki:edit']='עריכת דפי ויקי';
$string['ouwiki:view']='תצוגת ויקי';
$string['ouwiki:overridelock']='דפים נעולים שנדרסו';
$string['ouwiki:viewgroupindividuals']='Per-user subwikis: view same group';
$string['ouwiki:viewallindividuals']='Per-user subwikis: view all';
$string['ouwiki:editallsubwikis']=' עריכת כל התת ויקים הניראים כעת';
$string['ouwiki:viewcontributions']='הצגת רשימת תורמים המסודרת לפי משתמשים';
$string['commenton']='הערות על: ';
$string['commentcount']='{$a->count} הערות{$a->plural}';
$string['commentinfo']='{$a->commentlink}; latest {$a->date}';
$string['commentbyyou']='על ידיך';
$string['commentonsection']='הערה על פסקה';
$string['commentonpage']='הערה על דף';
$string['commentsonsection']='הערות על: $a';
$string['commentsonpage']='הערות';
$string['commentdeletedinfo']='מספר הערות מחוקות מוצגות ברשימה למטה. הן נראות רק למשתמשים בעלי אישור למחיקות. משתמשים רגילים אינם יכולים לראות אותן בכלל.';
$string['commentpostheader']='הוספת הערה';
$string['commentsubject']='נושא (אופציונלי)';
$string['commenttext']='הערה';
$string['commentpost']='פירסום הערה';
$string['commentdelete']='מחיקת הערה';
$string['commentundelete']='ביטול מחיקת הערה';
$string['commentoriginalsection']='Originally about deleted section: $a';
$string['commentblank']='יש לרשום מלל בחלון הערות';
$string['commentsolder']='{$a->count} הכי אחרונות מוצגות ({$a->link})';
$string['commentsviewall']='הצג הכל';
$string['commentsviewseparate']='הצגת הערות בדף נפרד';
$string['commentdeleteconfirm']='האם אתם בטוחים שברצונכם למחוק את ההערה? ברגע שהיא נמחקת אין אפשרות להחזירה.';
$string['access_commenter']='Commenter';

$string['wikirecentchanges']='שינוים בויקי';
$string['wikirecentchanges_from']='שינוים בויקי (דף $a)';
$string['advice_wikirecentchanges_changes']='<p>הטבלה למטה מציגה את כל השינויים שבוצעו בכל דפי ויקי זה, כשהם מסודרים לפי השינוי האחרון שבוצע. הגירסה האחרונה של כל דף הינה מודגשת.</p>
<p>בעזרת שימוש בקישורים אתם יכולים לצפות בדף כפי שהוא נראה לאחר שינוי מסויים, או לראות מה השתנה כרגע.</p>';
$string['advice_wikirecentchanges_changes_nohighlight']='<p>הטבלה למטה מציגה את כל השינויים שבוצעו בכל דפי ויקי זה, כשהם מסודרים לפי השינוי האחרון שבוצע.</p>
<p>בעזרת שימוש בקישורים אתם יכולים לצפות בדף כפי שהוא נראה לאחר שינוי מסויים, או לראות מה השתנה כרגע.</p>';
$string['advice_wikirecentchanges_pages']='<p>הטבלה הזאת מראה מתי כל דף צורף לויקי, כאשר היא מתחילה עם הדף שנוצר אחרון.</p>';
$string['wikifullchanges']='הצגת רשימת שינויים מלאה';
$string['tab_index_changes']='כל השינויים';
$string['tab_index_pages']='דפים חדשים';
$string['page']='דף';
$string['next']='שינויים ישנים יותר';
$string['previous']='שינויים חדשים יותר';

$string['newpage']='גירסה ראשונה';
$string['current']='נוכחית';
$string['currentversionof']='גירסה נוכחית של ';

$string['linkedfrom']='דפים שקשורים לדף זה';
$string['linkedfromsingle']='דף שקשור לדף זה';

$string['editpage']='עריכת דף';
$string['editsection']='עריכת פסקה';

$string['editingpage']='עריכת דף';
$string['editingsection']='עריכת פסקה: $a';

$string['historyfor']= 'היסטוריה עבור';
$string['historycompareaccessibility']='Select {$a->lastdate} {$a->createdtime}';

$string['timelocked_before']='ויקי זה כרגע נעול. אפשר לערוך אותו מ $a.';
$string['timelocked_after']='ויקי זה נעול כרגע וכבר אי-אפשר לערוך אותו יותר.';

$string['returntopage']='חזרה לדף ויקי';

$string['savetemplate']='שמירת ויקי כתבנית';
$string['template']='תבנית';

$string['contributionsbyuser']='תרומות כתיבה ע\"י משתמש';
$string['changebutton']='שינוי';
$string['contributionsgrouplabel']='קבוצה';
$string['nousersingroup']='בקבוצה שנבחרה אין משתמשים.';
$string['nochanges']='משתמשים אשר לא תרמו';
$string['contributions']='<strong>{$a->pages}</strong> דף חדש{$a->pagesplural}, <strong>{$a->changes}</strong> שינוי נוסף{$a->changesplural}.';

$string['entirewiki']='כל הויקי';
$string['onepageview']='באפשרותך לצפות בכל הדפים של ויקי זה לשם נוחות הדפסה או למען יצירת מסמך איזכור קבוע.';
$string['format_html']='צפייה מקוונת';
$string['format_rtf']='הורדה בפורמט word processor ';
$string['format_template']='הורדה כקובץ תבנית ויקי.';
$string['savedat']='נשמר ב $a';

$string['feedtitle']='{$a->course} ויקי: {$a->name} - {$a->subtitle}';
$string['feeddescriptionchanges']='השמת כל השינויים שנעשו בויקי לתוך רשימה. הירשמו ל-Feed זה אם ברצונכם לקבל עידכון בכל פעם שחל שינוי בויקי.';
$string['feeddescriptionpages']='השמת כל הדפים החדשים בויקי לתוך רשימה. הירשמו ל-Feed זה אם ברצונכם לקבל עידכון בכל פעם שמשתמש מוסיף דף חדש.';
$string['feeddescriptionhistory']='השמת כל השינויים שנעשו בדף ויקי זה לתוך רשימה. הירשמו ל-Feed זה אם ברצונכם לקבל עידכון בכל פעם שמשתמש עורך דף זה.';
$string['feedchange']='שונה ע\"י  {$a->name} (<a href=\'{$a->url}\'>צפייה בשינוי</a>)';
$string['feednewpage']='נוצר על ידי {$a->name}';
$string['feeditemdescriptiondate']='{$a->main} ב- {$a->date}.';
$string['feeditemdescriptionnodate']='{$a->main}.';
$string['feedsubscribe']='ברשותך להירשם ל-Feed הכולל את המידע הבא: <a href=\'{$a->atom}\'>Atom</a> או <a href=\'{$a->rss}\'>RSS</a>.';
$string['feedalt']='הירשם ל- Atom Feed.';


$string['olderversion']='גירסה ישנה יותר';
$string['newerversion']='גירסה חדשה יותר';


// reports pages
$string['reports']='דוחות שימוש בויקי';

$string['report_summaryreports']='דוחות סיכום';
$string['report_groupreports']='דוח קבוצה';
$string['report_userreports']='דוח משתמש';
$string['report_grouplabel']='בחירת קבוצה לקבלת דוח';

$string['report_grouptabletitle']='דוח קבוצה';
$string['report_group']='קבוצה';
$string['report_coursenum']='קורס';
$string['report_total']='סה\"כ';
$string['report_active']='פעיל';
$string['report_inactive']='לא פעיל';
$string['report_percentageparticipation']='השתתפות';
$string['report_totalpages']='סה\"כ דפים';
$string['report_editedpages']='דפים שעברו עריכה';
$string['report_uneditedpages']='דפים שלא עברו עריכה';
$string['report_edits']='עריכות';
$string['report_comments']='הערות';

$string['report_grouptabletitle']='פעילות קבוצה';
$string['report_user']='פעילות משתמש';
$string['report_username']='שם';
$string['report_timeonwiki']='ימים';
$string['report_createdpages']='דפים שנוצרו';
$string['report_additions']='תוספות';
$string['report_deletes']='מחיקות';
$string['report_otheredits']='עריכות אחרות';
$string['report_contributions']='סה\"כ תרומות בכתיבה';
$string['report_userstabletitle']='פעילות ע\"י משתמש';
$string['report_compareversions']='השוואת גירסאות';
$string['report_compare']='שינוים';

$string['report_editscommentsgraphtitle']='$a->ouw_bargraph1key עריכות ו $a->ouw_bargraph2key הערות';
$string['report_editedpagesgraphtitle']='$a->ouw_bargraph1key Edited pages by role';
$string['report_timelinetitle']='Timeline of edit activity by page';
$string['report_timelinepage']='דף';
$string['report_datetime']='תאריך ושעה';
$string['report_type']='סוג';
$string['report_new']='חדש';
$string['report_existing']='קיים';
$string['report_activitybydate']='פעילות עד תאריך';
$string['report_date']='תאריך';

$string['report_pagetabletitle']='פרטי דף';
$string['report_pagename']='דף';
$string['report_contributorcount']='תורמים לכתיבה';
$string['report_intensity']='אינטנסיביות';
$string['report_startday']='עריכה ראשונה';
$string['report_lastday']='עריכה אחרונה';
$string['report_wordcount']='מילים';
$string['report_linkcount']='קישורים';
$string['report_user_is_inactive']='$a לא עשה/תה כלום בויקי זה';
$string['report_emptywiki']='ויקי זה הינו ריק לחלוטין (אין בו דפים) ולכן אין מידע לדווח.';

$string['report_viewallgroups']='הצגת פרטי משתמש מכל קבוצה';
$string['report_timelinebar']='$a->date: $a->edits edits';

$string['report_intensityexplanation']='Intensity counts the number of edits that are &ldquo;in response to&rdquo; another user&rsquo;s edit, divided by the number of users that edited the page. An edit counts as &ldquo;in response to&rdquo; if the previous edit was by somebody else, so if you make two edits in a row that only counts as one edit for this calculation.';

$string['reportroles']='תפקידים כלולים בדוחות.';
$string['configreportroles']='רק משתמשים עם תפקידים אלה נחשבים ומוצגים בדוחות.';
$string['configreportroles_text']='Only users with these roles are counted and displayed on the reporting screens. This field must be a comma-separated list of role ID numbers. (To find the numbers, use the links from the \'Define roles\' screen and look in the URL.)';

$string['completionpagesgroup']='נחוצים דפים חדשים';
$string['completionpages']='משתמש חייב ליצור דפים חדשים:';
$string['completionpageshelp']='צריך לסיים את הדפים החדשים';
$string['completioneditsgroup']='עריכות נחוצות';
$string['completionedits']='משתמש חייב ליצור עריכות:';
$string['completioneditshelp']='צריך לסיים את העריכות.';

$string['ouwiki:comment']='הערה על דפי ויקי.';
$string['ouwiki:deletecomments']='מחיקת דף הערות ויקי.';
$string['ouwiki:deletepage']='מחיקת גירסאות דפי ויקי.';

$string['reverterrorversion'] = 'אין אפשרות לחזור לגירסה דף שאינה קיימת.';
$string['reverterrorcapability'] = 'אין לך את ההרשאה להחזיר את הגירסה הנוכחית לגירסה הקודמת.';
$string['revert'] = 'חזרה למצב קודם';
$string['revertversion'] = 'חזרה למצב קודם';
$string['revertversionconfirm']='<p>דף זה יוחזר למצב שבו הוא היה ב- $a, כאשר כל השינויים שנעשו מאז יושלכו לפח. השינויים שהושלכו יהיו עדיין זמינים בדף היסטוריה.</p><p>האם אתם בטוחים שאתם רוצים לחזור לגירסה הזאת של הדף?</p>';

$string['deleteversionerrorversion'] = 'אין אפשרות למחיקת גירסת דף אשר איננה קיימת.';
$string['viewdeletedversionerrorcapability'] = 'שגיאה בהצגת גירסת הדף.';
$string['deleteversionerror'] = 'שגיאה במחיקת הגירסה.';
$string['pagedeletedinfo']='מספר גרסאות מחוקות מוצגות ברשימה שלמטה. רק משתמשים המורשים למחיקת גרסאות יכולים לראות אותן. משתמשים רגילים לא רואים אותן בכלל.';
$string['undelete'] = 'ביטול מחיקה';
$string['advice_viewdeleted']='אתם צופים בגירסה מחוקה של דף זה.';

$string['csvdownload']='הורדה בפורמט spreadsheet (UTF-8 .csv)';
$string['excelcsvdownload']='הורדה בפורמט Excel-compatible (.csv)';

$string['closecomments']='סגירת הערות';
$string['closecommentform']='סגירת טופס הערות';

$string['create']='צרו דף חדש';
$string['createnewpage']='צרו דף חדש';
$string['typeinpagename']='הזינו את שם הדף';
$string['add']='הוסיפו לסוף הדף הקיים';
$string['typeinsectionname']='הזינו את כותרת הפסקה';
$string['addnewsection']='הוסיפו פסקה חדשה לדף זה';
$string['createdbyon'] = 'נוצר על ידי {$a->name} בתאריך {$a->date}';

$string['numedits'] = '$a עריכות';
$string['overviewnumentrysince1'] = 'כניסת ויקי חדשה מאז התחברות אחרונה.';
$string['overviewnumentrysince'] = 'כניסות ויקי חדשות מאז התחברות אחרונה.';

$string['pagenametoolong'] = 'שם הדף ארוך מידי. יש לבחור שם קצר יותר.';
?>
