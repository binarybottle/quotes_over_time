#!/usr/bin/python
"""Source: Reuters Alertnet"""

import time
import datetime

# NOTE: There are two sections in the grabnews.php code that are tailored to the news source
#           and are preceded by: "TAILORED TO SOURCE"
source_name = 'Reuters Alertnet'
domain_string    = 'mobile.alertnet.org' # /?_lite_=1'
num_days    = '0' #'14'  # '0' for entire archive
date_today = datetime.datetime.now().strftime("%Y%m%d")

#               ["","All"],
topics =     [
    ["BIRDFLU","Bird flu"],
    ["aidindex","Reuters"],
    ["index","Tsunami AidWatch"],
    ["HIVAID","AIDS pandemic"],
    ["BOOKS","Books"],
    ["CHILDPOLL","Child danger poll 2006"],
    ["152544","Children"],
    ["CHOLERA","Cholera"],
    ["342653","Climate and Weather"],
    ["CRISESPOLL","Crises poll 2005"],
    ["DENGUE","Dengue"],
    ["RESWARS","Diamonds and resource wars"],
    ["DISMIT","Disaster mitigation"],
    ["152547","Earthquakes"],
    ["EBOLA","Ebola"],
    ["feature","Feature"],
    ["floods","Floods"],
    ["307468","Food Security"],
    ["152538","Health"],
    ["IHULAW","International Humanitarian Law"],
    ["152535","Landmines"],
    ["LOCUSTS","Locusts"],
    ["MALARIA","Malaria"],
    ["MEDHUM","Media & Humanitarianism"],
    ["POLIO","Polio"],
    ["POLAID","Politics of Aid"],
    ["QUIZ","Quiz"],
    ["152593","Refugees & displacement"],
    ["AIDWATCH","Reuters Tsunami AidWatch"],
    ["SECURITY","Security"],
    ["TALKING","Talking points"],
    ["TECHNO","Technology"],
    ["TB","Tuberculosis"],
    ["224887","Viewpoints"],
    ["VOLCANO","Volcanoes"],
    ["WATER","Water"],
    ["WOMEN","Women"],
    ["152554","Working in Relief"]
]

emergencies = []

size_topics_topics = len(topics)
size_topics = len(topics) +len(emergencies)

# Regexp for extracting the URLs
url_regexp = r'(/thenews/newsdesk/)(?!index|<).*[.]htm'
#url_regexp = r'(/thenews/newsdesk/|/thefacts/reliefresources/)(?!index|<).*[.]htm'
url_append_pre  = 'http://' + domain_string  # String to append (left)
url_page_incr   = 10         # Number of links per referral page

# Regular expressions for extracting data from each URL's site 
# (Strings are treated as case-insensitive)
headline_regexp    = r'("artTitle"|"ANTitle")>(\S).*?<' # Regexp for extracting headline
datestring_regexp  = r'("newstime")>(\S).*?<'  # Regexp for extracting date

# Regexps for extracting article
#article_regexp = r'(inline article box end|article header end|mainimage end|"artTitle"|"ANTitle").*(article end|<br clear="all">|END: print_article|copyright)'
#article_regexp = r'(inline article box end|article header end|mainimage end|"artTitle"|"ANTitle").*?(article end|END: print_article)'
#article_start_regexp = r'(inline article box end|article header end|mainimage end|"artTitle"|"ANTitle"|\(Reuters\)(\f|\n|\r|\t| |\v)*-)'
article_start_regexp = r'(dne xob elcitra enilni|dne redaeh elcitra|dne egaminiam|"eltiTtra"|"eltiTNA"|-(\f|\n|\r|\t| |\v)*\)sretueR\()'
article_end_regexp = r'(article end|<br clear="all">|END: print_article|<p class="copyright">)' #|copyright)'
   
store_article      = 1           # Store article for text processing
store_regexp = r'.*[.]htm'           # Regexp for storing article
  
# Regexps for extracting quotes   
quote_delim_regexp = r'("|&quot;|&quot|&#8220|&#8221)'      # Quotation delimiter regexp
comma_quote_regexp = r'(,"|,&quot|,&#8221)'           # Comma + quote regexp
quote_regexp       = r'(("|&quot;|&quot)(\f|\n|\r|\t| |\v|.)*?("|&quot;|&quot|<p></p>))|((&#8220)(\f|\n|\r|\t| |\v|.)*?(<p></p>|&#8221))|(&#8216(\f|\n|\r|\t| |\v|.)*(?!&8216)(\f|\n|\r|\t| |\v|.)*?[.?!,]&#8217)'
puncs_regexp       = r'[.?!,]("|&quot;|&quot|<p></p>|&#8221)'       # Regexp for extracting punctuation before end quote
min_words_quote    = 3           # Minimum number of words for a quote


# NOTE: emergencies not included as of 20070131
emergencies_OLD = [
    ["AF_WEA","African extreme weather"],
    ["AF_LOC","African locust plague"],
    ["338813","Balkans reconstruction"],
    ["CA_TRO","Caribbean extreme weather"],
    ["CA_CON","Central African conflicts"],
    ["CA_POS","Central America post-war"],
    ["CA_HUN","Central American hunger"],
    ["152584","Chechnya war"],
    ["CN_FLO","China extreme weather"],
    ["CN_TOX","China toxic spill"],
    ["IN_ENC","Encephalitis in India"],
    ["ET_UNR","Ethiopia unrest"],
    ["GW_CHO","Guinea-Bissau cholera epidemic"],
    ["152575","Horn of Africa food crisis"],
    ["200529N_AL29","Hurricane Epsilon"],
    ["200512N_AL12","Hurricane Katrina"],
    ["200520N_AL20","Hurricane Stan"],
    ["200524N_AL24","Hurricane Wilma"],
    ["IR_QUA","Iran earthquake 2005"],
    ["LS_FOO","Lesotho food crisis"],
    ["MW_FOO","Malawi food crisis"],
    ["ID_MOL","Moluccas violence"],
    ["MZ_FOO","Mozambique food crisis"],
    ["PE_AFT","Peru after conflict"],
    ["CG_CON","Republic of Congo violence"],
    ["SA_FOO","S. African food crisis"],
    ["SZ_FOO","Swaziland food crisis"],
    ["TG_CRI","Togo crisis"],
    ["200525W_25W","Tropical storm 25W"],
    ["200527N_AL27","Tropical storm Gamma"],
    ["UZ_CRI","Uzbekistan crisis"],
    ["VE_TEN","Venezuela tension"],
    ["VN_MON","Vietnam Montagnard crisis"],
    ["WA_WAR","West African wars"],
    ["ID_CON","Aceh peace"],
    ["AF_REC","Afghan reconstruction"],
    ["AF_HUN","African hunger"],
    ["HIV_AFR","AIDS in Africa"],
    ["HIV_CAR","AIDS in Americas"],
    ["HIV_ASI","AIDS in Asia"],
    ["EE_HIV","AIDS in E.Europe/C.Asia"],
    ["ME_HIV","AIDS in M.East"],
    ["GL_HIV","AIDS pandemic"],
    ["AO_REC","Angola recovery"],
    ["GL_BIR","Bird flu"],
    ["BU_HUN","Burundi hunger"],
    ["BI_TRA","Burundi transition"],
    ["KH_REC","Cambodia recovery"],
    ["CA_FLO","Central African Republic troubles"],
    ["TD_HUN","Chad hunger"],
    ["TD_REB","Chad troubles"],
    ["152584","Chechnya war"],
    ["MU_CHI","Chikungunya"],
    ["152569","Colombia displacement"],
    ["CG_TEN","Congo (Brazzaville) troubles"],
    ["ZR_CON","Congo (DR) conflict"],
    ["Sd_DAR","Darfur conflict"],
    ["DJ_HUN","Djibouti hunger"],
    ["MJ_DNE","Dnestr-Moldova dispute"],
    ["EA_HUN","E. African hunger"],
    ["SD_INS","East Sudan insurgency"],
    ["266062","East Timor nation-building"],
    ["ER_HUN","Eritrea hunger"],
    ["EE_BOR","Eritrea-Ethiopia border"],
    ["ET_FLO","Ethiopia floods"],
    ["ET_HUN","Ethiopia hunger"],
    ["GG_OSS","Georgia, Abkhazia, S. Ossetia"],
    ["HA_UNR","Haiti unrest"],
    ["200605N_AL05","Hurricane Ernesto"],
    ["200512N_AL12","Hurricane Katrina"],
    ["200520N_AL20","Hurricane Stan"],
    ["IN_CLA","India's northeastern clashes"],
    ["IN_MAO","Indian Maoist violence"],
    ["SA_TID","Indian Ocean tsunami"],
    ["ID_EAR","Indonesia earthquake"],
    ["200605E_05E","Intense hurricane Daniel"],
    ["200610E_10E","Intense hurricane Ileana"],
    ["200611E_11E","Intense hurricane John"],
    ["571273","Iraq in turmoil"],
    ["IP_CON","Israeli-Palestinian conflict"],
    ["CI_UNR","Ivory Coast unrest"],
    ["ID_TSU","Java tsunami"],
    ["KA_DIS","Kashmir dispute"],
    ["KE_HUN","Kenya hunger"],
    ["KO_VIO","Kosovo violence"],
    ["LB_CRI","Lebanon crisis"],
    ["LS_HUN","Lesotho hunger"],
    ["LR_CRI","Liberian reconstruction"],
    ["GL_MAL","Malaria"],
    ["MW_HUN","Malawi hunger"],
    ["ML_HUN","Mali hunger"],
    ["ML_UNR","Mali unrest"],
    ["MZ_HUN","Mozambique hunger"],
    ["MY_DIS","Myanmar displacement"],
    ["NK_CON","Nagorno-Karabakh conflict"],
    ["NA_FOO","Namibia food crisis"],
    ["NE_INS","Nepal insurgency"],
    ["NE_FOO","Niger hunger"],
    ["NG_VIO","Nigeria violence"],
    ["KP_FAM","North Korea famine"],
    ["PK_VIO","Pakistan violence"],
    ["ID_PAP","Papua tensions"],
    ["PH_SEP","Philippines-Mindanao conflict"],
    ["RW_TEN","Rwanda legacy"],
    ["SA_HUN","S. African hunger"],
    ["SA_EAR","S. Asia earthquake"],
    ["SA_MON","S. Asia monsoon"],
    ["SN_INS","Senegal insurgency"],
    ["SO_HUN","Somalia hunger"],
    ["SO_PEA","Somalia troubles"],
    ["SD_PEA","South Sudan fragile peace"],
    ["LK_CON","Sri Lanka conflict"],
    ["246397","Sudan conflicts"],
    ["200601C_01C","Super typhoon Ioke"],
    ["200608W_08W","Super typhoon Saomai"],
    ["SZ_HUN","Swaziland hunger"],
    ["TH_VIO","Thailand violence"],
    ["200606E_06E","Tropical depression 06E"],
    ["200604N_AL04","Tropical depression AL04"],
    ["200610W_10W","Tropical storm Bopha"],
    ["200603N_AL03","Tropical storm Chris"],
    ["200607W_07W","Tropical storm Prapiroon"],
    ["200611W_11W","Tropical storm Wukong"],
    ["200609W_09W","Typhoon Maria"],
    ["UG_VIO","Uganda violence"],
    ["WA_HUN","W. African hunger"],
    ["WE_SAH","Western Sahara dispute"],
    ["ZM_FOO","Zambia food crisis"],
    ["ZW_CRI","Zimbabwe crisis"],
    ["ZW_HUN","Zimbabwe hunger"]
]
