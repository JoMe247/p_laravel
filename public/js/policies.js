$(document).ready(function () {

    // =========================================================================
    //   CONFIG GENERAL
    // =========================================================================
    const csrf = $('meta[name="csrf-token"]').attr('content');

    const $overlayNew = $('#policy-overlay');
    const $newBtn = $('#new-policy-btn');
    const $cancelNew = $('#policy-cancel-btn');
    const $saveNew = $('#policy-save-btn');
    const config = $('#policy-config');
    const storeUrl = config.data('store-url');

    const $overlayEdit = $('#policy-edit-overlay');
    const $overlayContent = $('#policy-edit-content');
    const $overlaySave = $('#policy-edit-save');

    const MAX_VEHICLES = 6;

    const COMMON_MAKES = [
        "Toyota", "Honda", "Ford", "Chevrolet", "Nissan", "Hyundai", "Kia", "Jeep", "RAM",
        "GMC", "Subaru", "Mazda", "Volkswagen", "BMW", "Mercedes-Benz", "Audi", "Lexus",
        "Tesla", "Dodge", "Chrysler", "Buick", "Cadillac", "Volvo", "Mitsubishi"
    ];

    // =========================================================================
    //   HELPERS GENERALES
    // =========================================================================
    const CARRIER_URLS = [
        { carrier: 'AGENTERO', url: 'https://my.agentero.com/experts/sign_in' },
        { carrier: 'BTIS', url: 'https://my.btisinc.com/m-login' },
        { carrier: 'LIBERTY MUTUAL', url: 'https://bi-quote.libertymutual.com/gw/menu' },
        { carrier: 'BHHC  BERKSHIRE  HOMESTATES COMPANIES', url: 'https://auth.bhhc.com/' },
        { carrier: 'THE GUARD BERKSHIRE', url: 'https://gigezrate.guard.com/' },
        { carrier: 'MARKEL', url: 'https://account.markel.com/' },
        { carrier: 'THE HARTFORD', url: 'https://account.thehartford.com/agent/login?goto=https:%2F%2Fagency.thehartford.com:443%2Fhome' },
        { carrier: 'Am Trust', url: 'https://auth.amtrustgroup.com/AuthServer/account/login' },
        { carrier: 'TRAVELERS', url: 'https://signin.travelers.com/' },
        { carrier: 'HAGERTY', url: 'https://login.hagerty.com/identity/Login' },
        { carrier: 'LANCER INSURANCE', url: 'https://login.lancerinsurance.com/cgi-bin/login.cgi' },
        { carrier: 'CNA SURETY BOND', url: 'https://www.cnasurety.com/cna/guest/cnasurety/' },
        { carrier: 'CNA CENTRAL', url: 'https://www.cnacentral.com/cnac/servlet/LoginServlet?html_page=%2F%2Fhtml%2F%2Fpublic_home.html' },
        { carrier: 'TAPCO', url: 'https://secure.gotapco.com/#/login' },
        { carrier: 'PERSONAL UMBRELLA.COM', url: 'https://www.personalumbrella.com/' },
        { carrier: 'CAT COVERAGE.COM', url: 'https://portal.catcoverage.com/all_policies_pay.aspx' },
        { carrier: 'EMCOMPASS', url: 'https://dashboard.encompassinsurance.com/foragents/agent_home.jsp' },
        { carrier: 'PHILADELPHIA INSURANCE COMPANIES', url: 'https://www.phly.com/products/Default.aspx' },
        { carrier: 'TEXAS MUTUAL', url: 'https://compnow.texasmutual.com/agenthome/' },
        { carrier: 'FOREMOST STAR', url: 'https://www.foremostagent.com/ia/portal/login' },
        { carrier: 'TOKIO MARINE', url: 'https://www.artisanedge.com/login' },
        { carrier: 'BRISTOL WEST', url: 'https://www.iaproducers.com/Producers/FMLogin.aspx?ReturnUrl=%2fProducers%2fdefault.aspx' },
        { carrier: 'Risk Placement Services (RPS)', url: 'https://www.rpscyber.com/iau_live/eis/rps.html#login' },
        { carrier: 'ARGENIA', url: 'https://crcgroupsso.b2clogin.com/crcgroupsso.onmicrosoft.com/b2c_1_crcagentsignin/oauth2/v2.0/authorize?client_id=a3c3d1ac-575a-4ddf-9e37-1e7a0f7201e8&redirect_uri=https%3A%2F%2Fquoting.crcgroup.com%2Fsignin-oidc&response_type=id_token&scope=openid%20prof' },
        { carrier: 'DAIRYLAND INSURANCE', url: 'https://agent.dairylandagent.com/web/dashboard/my-dashboard' },
        { carrier: 'EMPLOYERS', url: 'https://eaccess.employers.com/home' },
        { carrier: 'JENCAP', url: 'https://www.jencaphoustonportal.com/?from_login=1' },
        { carrier: 'MexiPass', url: 'https://login.mexipass.com/login?state=hKFo2SBTQWV3NUFLUFN0SE5lTDJBTEhGNlZKRkdhdWVsZjZndKFupWxvZ2luo3RpZNkgUkpFRVhhUHZSaUFDNVFRUlZoczhMNlc5X3RqVFEyYTajY2lk2SBuODRjZUxMd1l4R0lLeFhMOUxpUzd5eElMR281eU9oMg&client=n84ceLLwYxGIKxXL9LiS7yxILGo5yOh2&protocol=saml' },
        { carrier: 'PBS', url: 'https://www.pbsnetaccess.com/EntityLogin.aspx?portfolio=931' },
        { carrier: 'APPALACHIAN UNDEWRITERS', url: 'https://auiagents.com/Profile/Edit' },
        { carrier: 'THIMBLE BROKER', url: 'https://broker.thimble.com/dashboard/home' },
        { carrier: 'TEXAS RANGER', url: 'https://producers.warriorinsurancenetwork.com' },
        { carrier: 'PROGRESSIVE', url: 'https://www.foragentsonly.com/login/' },
        { carrier: 'SNAP AGENT CODE 421041000', url: 'https://www.snapmga.com/is/root/' },
        { carrier: 'NATIONAL GENERAL', url: 'https://natgenagency.com/Login.aspx?Menu=Login' },
        { carrier: 'KEMPER INFINITY', url: 'https://agent.kemper.com/auto/ap/home/show?canViewDailyReport=true&isAutoCa=false&isAuto=true&env=p0' },
        { carrier: 'FALCON', url: 'https://agency.falconinsgroup.com/#!' },
        { carrier: 'FENIX AGENCY', url: 'https://opas.fenixga.com/Production/Login/PromptAnonymous.aspx' },
        { carrier: 'AUTO MGA', url: 'https://automga.informinshosting.com/pms/admin/app.aspx' },
        { carrier: 'CONIFFER', url: 'https://portal.sycins.com' },
        { carrier: 'BLUE FIRE IGNITE', url: 'https://producer.bluefireinsurance.com/Account/Login' },
        { carrier: 'commonwealth', url: 'https://agent.commonwealthcasualty.com/login.jsp' },
        { carrier: 'ALINSCO', url: 'https://agents.empowerins.com/LogOn.aspx?ReturnUrl=%2fAgent%2fdefault.aspx' },
        { carrier: 'CONNECT', url: 'https://tx.connectinsurance.com/prod/index.php?page=' },
        { carrier: 'BLUE FIRE AGRESSIVE', url: 'https://pts.live.aggressiveusa.com/logIn.cfm' },
        { carrier: 'AssuranceAmerica', url: 'https://auto.assuranceamerica.com/login' },
        { carrier: 'UNITED AUTO', url: 'https://tx.uaig.net/agents/cgi-dta/ilogin.mac/main' },
        { carrier: 'AMWINS', url: 'https://osis.amwinsauto.com/prod/' },
        { carrier: 'VENTURE AGEN CODE 1282000', url: 'https://ventureinsga.net/is/root/logon/index.cfm?EUDATA=q0JvK5Q817XAboDlK2Y2FsGbGL582sIShFjlYbbbv%2Fkew3cQ8ZFcGdpqcuQqcYZ9XgRdQO6vU0s8Mn1jU%2BVyverx1o3iZZaDx2enJUPJ2U9pmh%2BeYtXPe5n27k0g%2FSVX' },
        { carrier: 'ACACIA', url: 'https://login.acaciamga.com' },
        { carrier: 'ALPINE RIO INSURANCE', url: 'https://agents.alpinerio.com/Login.aspx' },
        { carrier: 'BRECKENRIDGE', url: 'https://auto.breckgen.com/prod/index.php?page=policyAccess' },
        { carrier: 'HALLMARK', url: 'https://app.hplagent.com/Hallmark/(S(znbth5va0xl04ak4325z5v0t))/controlloader.aspx?p=Agency' },
        { carrier: 'LONE STAR', url: 'https://lonestar.live.ptsapp.com/logIn.cfm' },
        { carrier: 'HILLCO   AGENTE CODE 233210', url: 'https://hillcoga.net/is/root/logon/index.cfm' },
        { carrier: 'TRINITY AGENT CODE 2140186001', url: 'https://auto.tttmga.com/is/root/logon/index.cfm' },
        { carrier: 'ATTUNE', url: 'https://app.attuneinsurance.com/login' },
        { carrier: 'NOBLE AGENTE CODE 323200', url: 'https://nobleinsga.net/is/root/logon/index.cfm' },
        { carrier: 'GAINSCO', url: 'https://portal.gainscoconnect.com/Secured/Policy/PolicySearch.aspx' },
        { carrier: 'QUALITAS MCALLEN AUTO TEXAS', url: 'https://producer.qualitasinsurance.com/pms/admin/admin.aspx?tkn=5jTNgXAkddU%3d' },
        { carrier: 'THE GENERAL', url: 'https://www.pgac.com/mars/login.jsp?logout=true' },
        { carrier: 'QUALITAS SEGURO VIAJERO USA', url: 'http://bordermex.net/' },
        { carrier: 'QUALITAS AUTOS MEX', url: 'http://agentes.qualitasinsurance.com/#/login' },
        { carrier: 'VICTOR DOVTAIL', url: 'https://www.victorinsurance.com/us/login.html' },
        { carrier: 'QUANTUM ALLIANCE', url: 'https://quantum.live.ptsapp.com/logIn.cfm' },
        { carrier: 'BOND RAMP', url: 'https://bondramp.ufcic.com/' },
        { carrier: 'SGA', url: 'https://insurelight.net/default_mx2.aspx' },
        { carrier: 'ITC TURBO RATER', url: 'https://www.turborater.com/login/login.aspx?&returnurl=https%3a%2f%2fautorating.turborater.com%2fCurrent%2fAUComparisonPageNew.aspx' },
        { carrier: 'SWYFFT', url: 'https://swyfft.com/login' },
        { carrier: 'ENTEGRA', url: 'https://producer.entegrageneral.com/pms/login.aspx?tkn=gtTC1dGFQPGZyClDIsZ6kxqyzL%2b6d9SS4SBeLnuqNpzKxWhrl8jMmb6G3lfO1Kq5CjWxuH5NX5c%3d' },
        { carrier: 'FIRST CONNECT INSURANCE', url: 'https://portal.firstconnectinsurance.com/signin' },
        { carrier: 'GBLI VACANTEXPRESS', url: 'https://app1.uai-sys.com/express/default.aspx' },
        { carrier: 'USG', url: 'https://usginslink.com/Account/Login?ReturnUrl=%2F' },
        { carrier: 'HOMEOWNERS OF AMERICA', url: 'https://hoaic60.live.ptsapp.com/Login.cfm' },
        { carrier: 'RLI Insurance Company bond', url: 'https://www.mybondapp.com/225940190/Auth' },
        { carrier: 'ARKAY AUTO WARRANTY', url: 'https://arkay.info/FC76797' },
        { carrier: 'ISC', url: 'https://isc.onlinemga.com/amp/login' },
        { carrier: 'AON EDGE', url: 'https://c68-prod.diamondasaservice.com/DiamondWeb/(S(jeoayvrtqvwsit05ttaoorjo))/Agency?cllo=1' },
        { carrier: 'BOUNDLESS RIDER', url: 'https://www.boundlessrider.com/' },
        { carrier: 'COVERTREE', url: 'https://agent.covertree.com/auth/login' },
        { carrier: 'RAINWALK', url: 'https://agent.rainwalk.io/login' },
        { carrier: 'TRAWICK', url: 'https://agents.trawickinternational.com/Account/Login?ReturnUrl=%2F' },
        { carrier: 'spectrum', url: 'https://spectrumenterprise.net/login?signed-out=true' },
        { carrier: 'PATRIOT GENERAL AGENCY', url: 'https://producer.patriotmga.com/pms/login.aspx?tkn=DL0BSsAbrvs3HFZDxenqPK7Q0FS8KEVoQ7S3ZhIVTJp4oIfBzs92MWduEPoGh766Y8r7jabffb4%3d' },
        { carrier: 'EOS', url: 'https://www.eosadvisor.com/user/signin' },
        { carrier: 'ETHOS', url: 'https://agents.ethoslife.com/login' },
        { carrier: 'APPULATE', url: 'https://volta.appulate.com/signin' },
        { carrier: 'IDIQ', url: 'https://partner.idiq.com/Account/Login/' },
        { carrier: 'Bass Underwriters', url: 'https://app.bassuw.com/auth' },
        { carrier: 'LONESTAR MGA', url: 'https://www.lonestarmga.com/ProducerLogin' },
        { carrier: 'ELEPHANT', url: 'https://agency.elephant.com/' },
        { carrier: 'vested networks', url: 'https://nova.vestednetworks.com/portal/' },
        { carrier: 'PIE INSURANCER COMMERCIAL AUTO', url: 'https://partner.pieinsurance.com/sign-in' },
        { carrier: 'ARTESIA UNDERWRITERS', url: 'https://auto.artesiainsgroup.com/portal/security/login/prod' },
        { carrier: 'ROOT', url: 'https://agents.joinroot.com/login' },
        { carrier: 'LOOP', url: 'https://agentportal.ridewithloop.com/agent-portal/login' },
        { carrier: 'LEMONADE', url: 'https://blender-agents.lemonade.com/agents/sign_in' },
        { carrier: 'NOVELLA', url: 'https://broker.bynovella.com/signin' },
        { carrier: 'NORMANDY', url: 'https://quoting.normandyins.com/PUMAA/#/' },
        { carrier: 'MGT INSURANCE', url: 'https://www.mgtinsurance.com/' },
        { carrier: 'RAINBOW INSURANCE', url: 'https://app.userainbow.com/auth/login' },
        { carrier: 'THREE INSURANCE', url: 'https://login.agents.threeinsurance.com/u/login?state=hKFo2SBPdGNXRG1uVXlTd2FFdldnNlV0NlNZTG1wbllNWTNxTqFur3VuaXZlcnNhbC1sb2dpbqN0aWTZIDRGWFUyYS1mYUR3R1FNZlVMa2tkb3B3UDVjSjBYaDlpo2NpZNkgUW9NdjdUdFh4T2lRUmR2WEpyalhuYmdVN2hHbFB5djA' },
        { carrier: 'FUTURISTIC UDERWRITERS', url: 'https://c92-prod.diamondasaservice.com/DiamondWeb/(S(1afepfoujehg0v04e3yfgmzc))/Agency?cllo=1' },
        { carrier: 'TOWER HILL', url: 'https://auth.thig.com/am/XUI/?realm=/alpha&spEntityID=https%3A%2F%2Fportal.thig.com%2Fsaml%2Fmetadata&goto=https%3A%2F%2Fauth.thig.com%2Fam%2Fsaml2%2Fjsp%2FidpSSOInit.jsp%3FmetaAlias%3D%2Falpha%2FProduction%26spEntityID%3Dhttps%3A%2F%2Fportal.thig.com%2Fs' },
        { carrier: 'PARAGON INSURANCE', url: 'https://quoteparagon.com/#/login/signin' },
        { carrier: 'STAR MUTUAL RRG', url: 'https://app.myadl.com/login' },
        { carrier: 'COVER BADGER', url: 'https://app.coverbadger.com/app/' },
        { carrier: 'LAMAR', url: 'https://lamargenagency.com/DiamondWeb/(S(n2g1dfectge0v3knxezqs333))/LamarGenAgency?cllo=1' },
        { carrier: 'POUCH', url: 'https://agent.pouchinsurance.com/Public/AgentLogin' },
        { carrier: 'BRIDGER', url: 'https://producer.bridgerins.com/pms/login.aspx?tkn=nVNogqFNBZ12Q2jYlytwPCrp3E%2ftW9cYlL7DGfRw59j0t3Lw9SKIvloFxbEScRbDa6fh6xMxFDE%3d' },
        { carrier: 'KEMPER HISPANO TAX', url: 'https://agent.kemper.com/auto/ap/home/show?canViewDailyReport=true&isAutoCa=false&isAuto=true&env=p0' },
        { carrier: 'SEA HARBOR INSURANCE', url: 'https://seaharbor.insuroratlas.com/login/' },
        { carrier: 'INVO UNDERWRITENG', url: 'https://agents.invounderwriting.com/login' },
        { carrier: 'THORE INSURANCE', url: 'https://app.thore.exchange/login' },
        { carrier: 'ICW GROUP INSURANCE', url: 'https://agentportal.icwgroup.com/login' },
        { carrier: 'MENDOTA INSURANCE (ADVANTAGE)', url: 'https://agent.advantageauto.com/diamondweb/(S(1nud10vzo5pcmxrtesya23at))/agency' },
        { carrier: 'OPEN ROAD INSURANCE', url: 'https://prod-openroad-apps.digital1st.io/auth/realms/prod-openroad/protocol/openid-connect/auth?response_type=code&client_id=MajescoApps&redirect_uri=https%3A%2F%2Fprod-openroad-apps.digital1st.io%2Fagentlogin%2Findex&state=3c695292-1872-47c1-b26a-b4ae6fb' },
        { carrier: 'ITC TURBO 2 USUARIO', url: 'https://www.turborater.com/login/login.aspx?&returnurl=https%3a%2f%2fautorating.turborater.com%2fCurrent%2fwelcomepage.aspx%3flogout%3dtrue' },
        { carrier: 'KANGURO', url: 'https://agency.kanguroseguro.com/login' },
        { carrier: 'GEICO', url: 'https://gateway.geico.com/' },
        { carrier: 'HIPPO', url: 'https://producer.myhippo.com/v2/login' },
        { carrier: 'vertafone geico  account id 169094', url: 'https://www.sircon.com/login.jsp?accountType=business' },
        { carrier: 'BAMBOO', url: 'https://agent-auth.bambooinsurance.com/u/login?state=hKFo2SBRZnlIUGd2UDhvb2sxaVZjSUNEUFJ0NDN5aDdZWGVYYqFur3VuaXZlcnNhbC1sb2dpbqN0aWTZIDBsODFfbmZFakUyQzZ3eEV1OGszODk5VmZvSkxTT2tko2NpZNkgeThRYkdHTzZQN1BsQW8zSnFveTk1dEdsZ2dkb1pWSUg' },
        { carrier: 'NRG ENERGY', url: 'https://manage.nrg.com/myaccount/#/profile' },
        { carrier: 'PBS AUI Direct Bill.', url: 'http://www.gotopbs.com/auidirectbill' },
        { carrier: 'ICAT', url: 'https://producer.icat.com/icatsss/login' },
        { carrier: 'AGUILA DORADA MGA', url: 'https://auto.ad-ga.com/portal/security/login/prod' },
        { carrier: 'Biberk', url: '' },
    ];

    function normalizeLikeValue(value) {
        return (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function getMeaningfulWords(value) {
        const stopWords = new Set([
            'llc', 'inc', 'co', 'company', 'insurance', 'ins', 'agency', 'general',
            'mga', 'services', 'service', 'group', 'auto', 'mutual', 'specialty',
            'county', 'corp', 'corporation', 'the', 'and', 'of'
        ]);

        return normalizeLikeValue(value)
            .split(' ')
            .filter(word => word.length > 1 && !stopWords.has(word));
    }

    function isFlexibleMatch(searchValue, targetValue) {
        const searchNorm = normalizeLikeValue(searchValue);
        const targetNorm = normalizeLikeValue(targetValue);

        if (!searchNorm || !targetNorm) return false;

        if (targetNorm.includes(searchNorm) || searchNorm.includes(targetNorm)) {
            return true;
        }

        const searchWords = getMeaningfulWords(searchValue);
        const targetWords = getMeaningfulWords(targetValue);

        if (!searchWords.length || !targetWords.length) return false;

        const commonWords = searchWords.filter(word => targetWords.includes(word));
        return commonWords.length > 0;
    }

    function findCarrierUrl(searchValue) {
        if (!searchValue) return '';

        let match = CARRIER_URLS.find(row => isFlexibleMatch(searchValue, row.carrier));
        if (match) return match.url || '';

        match = CARRIER_URLS.find(row => isFlexibleMatch(searchValue, row.url));
        if (match) return match.url || '';

        return '';
    }

    // Año y Marcas

    function fillMakeSelect($makeSel, selected = '') {
        $makeSel.empty().append('<option value="">Seleccione marca</option>');
        COMMON_MAKES.forEach(make => {
            $makeSel.append(`<option value="${make}">${make}</option>`);
        });
        $makeSel.append('<option value="other">Other</option>');

        if (selected) {
            if ($makeSel.find(`option[value="${selected}"]`).length === 0) {
                $makeSel.append(`<option value="${selected}">${selected}</option>`);
            }
            $makeSel.val(selected);
        }
    }

    function fillYearSelect($yearSel, selected = '') {
        const currentYear = new Date().getFullYear();

        $yearSel.empty().append('<option value="">Seleccione</option>');
        for (let y = currentYear; y >= 1980; y--) {
            $yearSel.append(`<option value="${y}">${y}</option>`);
        }
        $yearSel.append('<option value="other">Other</option>');

        if (selected) {
            if ($yearSel.find(`option[value="${selected}"]`).length === 0) {
                $yearSel.append(`<option value="${selected}">${selected}</option>`);
            }
            $yearSel.val(selected);
        }
    }

    function loadModelsForMake($card, make, selectedModel = '', isEdit = false) {
        const $modelSel = $card.find(isEdit ? '.edit_model_select' : '.model-select');

        $modelSel.empty().append('<option value="">Cargando modelos...</option>');

        if (!make || make === 'other') {
            $modelSel.empty().append('<option value="">Seleccione modelo</option>');
            return;
        }

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMake/${encodeURIComponent(make)}?format=json`,
            function (res) {
                $modelSel.empty().append('<option value="">Seleccione modelo</option>');

                if (!res.Results?.length) {
                    $modelSel.append('<option value="">No hay modelos</option>');
                    return;
                }

                const models = [...new Set(res.Results.map(x => x.Model_Name).filter(Boolean))].sort();
                models.forEach(model => {
                    $modelSel.append(`<option value="${model}">${model}</option>`);
                });

                $modelSel.append('<option value="other">Other</option>');

                if (selectedModel) {
                    if ($modelSel.find(`option[value="${selectedModel}"]`).length === 0) {
                        $modelSel.append(`<option value="${selectedModel}">${selectedModel}</option>`);
                    }
                    $modelSel.val(selectedModel);
                }
            }
        );
    }

    function updateImageForCard($card, make, model, year) {
        if (!make || !model || !year || make === 'other' || model === 'other' || year === 'other') return;

        const url =
            `https://cdn.imagin.studio/getImage?customer=img&make=${encodeURIComponent(make)}` +
            `&modelFamily=${encodeURIComponent(model)}&modelYear=${year}` +
            `&paintdescription=white&angle=28&zoomtype=fullscreen`;

        const id = $card.data('id');
        $(`#thumb_${id}`).css('background-image', `url('${url}')`);
    }

    function updateEditImage(index) {
        const $card = $(`.vehicle-edit-card[data-index="${index}"]`);

        let year = $card.find('.edit_year_select').val();
        if (!year || year === 'other') {
            year = ($card.find('.edit_year_other').val() || '').trim();
        }

        let make = $card.find('.edit_make_select').val();
        if (!make || make === 'other') {
            make = ($card.find('.edit_make_other').val() || '').trim();
        }

        let model = $card.find('.edit_model_select').val();
        if (!model || model === 'other') {
            model = ($card.find('.edit_model_other').val() || '').trim();
        }

        if (!year || !make || !model) return;

        const imgUrl =
            `https://cdn.imagin.studio/getImage?customer=img&make=${encodeURIComponent(make)}` +
            `&modelFamily=${encodeURIComponent(model)}&modelYear=${year}` +
            `&paintdescription=white&angle=28&zoomtype=fullscreen`;

        $(`#vehicle_edit_thumb_${index}`).css('background-image', `url('${imgUrl}')`);
    }

    function initYearsForCard($card) {
        const $yearSel = $card.find('.year-select');
        fillYearSelect($yearSel);
    }

    function initEditCardValues($card, vehicle) {
        const year = vehicle?.year || '';
        const make = vehicle?.make || '';
        const model = vehicle?.model || '';

        fillYearSelect($card.find('.edit_year_select'), year);
        fillMakeSelect($card.find('.edit_make_select'), make);

        if (make) {
            loadModelsForMake($card, make, model, true);
        } else {
            $card.find('.edit_model_select').html('<option value="">Seleccione modelo</option>');
        }

        if (year && $card.find('.edit_year_select option[value="' + year + '"]').length === 0) {
            $card.find('.edit_year_select').hide();
            $card.find('.edit_year_other').show().val(year);
        }

        if (make && $card.find('.edit_make_select option[value="' + make + '"]').length === 0) {
            $card.find('.edit_make_select').hide();
            $card.find('.edit_make_other').show().val(make);
        }

        if (model) {
            setTimeout(() => {
                if ($card.find('.edit_model_select option[value="' + model + '"]').length === 0) {
                    $card.find('.edit_model_select').hide();
                    $card.find('.edit_model_other').show().val(model);
                }
                updateEditImage($card.data('index'));
            }, 400);
        } else {
            updateEditImage($card.data('index'));
        }
    }

    function createVehicleCardHtml(id) {
        return `
            <div class="vehicle-card" data-id="${id}">
                <div id="thumb_${id}" class="vehicle-thumb"></div>

                <div class="vehicle-field">
                    <label>VIN (opcional)</label>
                    <input type="text" class="vin-input">
                </div>

                <div class="vehicle-field">
                    <label>Año</label>
                    <select class="year-select">
                        <option value="">Seleccione</option>
                    </select>
                    <input type="text" class="year-other" style="display:none;" placeholder="Otro año">
                </div>

                <div class="vehicle-field">
                    <label>Make</label>
                    <select class="make-select">
                        <option value="">Seleccione</option>
                    </select>
                    <input type="text" class="make-other" style="display:none;" placeholder="Otra marca">
                </div>

                <div class="vehicle-field">
                    <label>Model</label>
                    <select class="model-select">
                        <option value="">Seleccione</option>
                    </select>
                    <input type="text" class="model-other" style="display:none;" placeholder="Otro modelo">
                </div>

                <div class="vehicle-delete-btn">Eliminar Vehículo</div>
            </div>
        `;
    }

    function createVehicleEditCardHtml(index, vehicle = {}) {
        const vin = vehicle.vin || '';

        return `
            <div class="vehicle-edit-card" data-index="${index}">
                <div class="vehicle-edit-thumb" id="vehicle_edit_thumb_${index}"></div>

                <label>VIN</label>
                <input type="text" class="edit_vin" value="${vin}">

                <label>Year</label>
                <select class="edit_year_select">
                    <option value="">Seleccione</option>
                </select>
                <input type="text" class="edit_year_other" style="display:none;" placeholder="Otro año">

                <label>Make</label>
                <select class="edit_make_select">
                    <option value="">Seleccione marca</option>
                </select>
                <input type="text" class="edit_make_other" style="display:none;" placeholder="Otra marca">

                <label>Model</label>
                <select class="edit_model_select">
                    <option value="">Seleccione modelo</option>
                </select>
                <input type="text" class="edit_model_other" style="display:none;" placeholder="Otro modelo">

                <div class="vehicle-delete-btn">Eliminar Vehículo</div>
            </div>
        `;
    }

    function detectCarrier(policy) {
        if (!policy) return '';

        policy = policy.trim();

        if (policy.startsWith('HTX')) return 'AUTOMGA';
        if (policy.startsWith('03MGEP')) return 'Gainsco';
        if (policy.startsWith('NXT')) return 'Next Insurance';
        if (policy.startsWith('BGS')) return 'Breckenridge General Agency';
        if (policy.startsWith('HGA')) return 'Hillco General Agency';
        if (/^TX.{7}$/.test(policy)) return 'The General';
        if (/^TXO.{8}$/.test(policy)) return 'Blue Fire - Tejas Seguros Insurance';
        if (policy.startsWith('NGA')) return 'Noble General Agency';
        if (policy.startsWith('QBTPA')) return 'Qualitas Macallen';
        if (policy.startsWith('PA')) return 'Pronto General Agency Ltd.';
        if (/^TXCA.{7}$/.test(policy)) return 'Pouch Insurance';
        if (/^TXCA.{11}$/.test(policy)) return 'Pouch Insurance';
        if (/^\d{7}$/.test(policy)) return 'Kemper Specialty';
        if (/^\d{11}$/.test(policy)) return 'Kemper Specialty';
        if (/^\d{10}$/.test(policy)) return 'National General';
        if (/^\d{9}$/.test(policy)) return 'Progressive County Mutual Ins Co';
        if (/^\d{8}$/.test(policy)) return 'Aspen Managing General Agency';
        if (/^\d{6}$/.test(policy)) return 'Hillco General Agency Llc';
        if (policy.startsWith('RPE')) return 'Entegra General';
        if (policy.startsWith('148')) return 'Fenix General Agency Llc';
        if (policy.startsWith('DVR')) return 'Argenia';
        if (policy.startsWith('TTXL')) return 'Trinity General Agency';
        if (policy.startsWith('TXR')) return 'Hallmark County Mutual Insurance Company';
        if (policy.startsWith('TPR')) return 'Lonestar';
        if (policy.startsWith('2Y-')) return 'The General';
        if (policy.startsWith('N9')) return 'Biberk Business Insurance';
        if (policy.startsWith('DWC')) return 'Thimble Insurance Services';
        if (policy.startsWith('IBL')) return 'Thimble Insurance Services';
        if (policy.startsWith('QMCA')) return 'Qualitas Mexico';
        if (policy.startsWith('HBW')) return 'Appa';
        if (policy.startsWith('2ACP')) return 'Appa';
        if (policy.startsWith('JSG')) return 'Markel Ins Co';
        if (policy.startsWith('C-0')) return 'Risk Placement Services';
        if (policy.startsWith('GLSI')) return 'ISC';
        if (policy.startsWith('SCB')) return 'ISC';
        if (policy.startsWith('WC')) return 'Biberk Business Insurance';
        if (policy.startsWith('TXLP')) return 'Lamar General A';
        if (policy.startsWith('42-')) return 'BTIS';
        if (policy.startsWith('QAQ')) return 'Quantum Alliance';
        if (policy.startsWith('QAC')) return 'Quantum Alliance';
        if (policy.startsWith('PPTX')) return 'Aspen Managing General Agency';
        if (policy.startsWith('CCB')) return 'Connect Insurance';
        if (policy.startsWith('TRG')) return 'Connect MGA';
        if (policy.startsWith('TXA')) return 'Commonwealth Casualty Company';
        if (policy.startsWith('EAB')) return 'Alinsco Insurance Company';
        if (policy.startsWith('EAL')) return 'Alinsco Insurance Company';
        if (policy.startsWith('EAA')) return 'Alinsco Insurance Company';
        if (policy.startsWith('AIM')) return 'Acacia Insurance Managers';
        if (policy.startsWith('TSGL')) return 'Usg Insurance Services Inc.';
        if (policy.startsWith('44-PPA')) return 'Patriot General Agency';
        if (policy.startsWith('RTX')) return 'Assurance America';
        if (policy.startsWith('PRH')) return 'Amwins Specialty Auto';
        if (policy.startsWith('QNBPA')) return 'Qualitas McAllen';
        if (policy.startsWith('TXOP')) return 'Blue Ignite';
        if (policy.startsWith('THV')) return 'Conifer Insurance Company';

        return '';
        
    }

    function markFieldError($field, hasError) {
        if (!$field || !$field.length) return;
        $field.css('border', hasError ? '1px solid red' : '');
    }

    function setRequiredPolicyFields(prefix = '') {
        const fields = [
            `${prefix}pol_number`,
            `${prefix}pol_carrier`,
            `${prefix}pol_url`,
            `${prefix}pol_expiration`,
            `${prefix}pol_eff_date`,
            `${prefix}pol_added_date`,
            `${prefix}pol_due_day`,
            `${prefix}pol_status`,
            `${prefix}pol_agent_record`
        ];

        fields.forEach(id => {
            const $field = $('#' + id);
            if ($field.length) {
                $field.prop('required', true);
            }
        });
    }

    function validateRequiredFields(prefix = '') {
        const fields = [
            `${prefix}pol_number`,
            `${prefix}pol_carrier`,
            `${prefix}pol_url`,
            `${prefix}pol_expiration`,
            `${prefix}pol_eff_date`,
            `${prefix}pol_added_date`,
            `${prefix}pol_due_day`,
            `${prefix}pol_status`,
            `${prefix}pol_agent_record`
        ];

        let firstInvalid = null;
        let isValid = true;

        fields.forEach(id => {
            const $field = $('#' + id);
            if (!$field.length) return;

            const value = ($field.val() || '').trim();
            const isEmpty = value === '';

            markFieldError($field, isEmpty);

            if (isEmpty && !firstInvalid) {
                firstInvalid = $field;
                isValid = false;
            }
        });

        if (firstInvalid) {
            firstInvalid.focus();
        }

        return isValid;
    }

    // =========================================================================
    //   REQUIRED CAMPOS NUEVA POLICY
    // =========================================================================
    setRequiredPolicyFields('');

    // =========================================================================
    //   AUTOCARRIER EN TIEMPO REAL
    // =========================================================================
    $(document).on('input', '#pol_number, #edit_pol_number', function () {

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (err) {
                return false;
            }
        }


        const policy = $(this).val();
        const carrier = detectCarrier(policy);
        const foundUrl = findCarrierUrl(carrier);

        if (this.id === 'pol_number') {
            $('#pol_carrier').val(carrier);
            $('#pol_url').val(foundUrl);

            if(isValidUrl(foundUrl)){
                // console.log("enlace valido"); // true
                document.getElementById("company-website-button").setAttribute("onclick",`window.open('${foundUrl}')`);
                document.getElementById("company-website-button").style.display = "flex";
            }else{
                document.getElementById("company-website-button").style.display = "";
            }
            
        } else if (this.id === 'edit_pol_number') {
            $('#edit_pol_carrier').val(carrier);
            $('#edit_pol_url').val(foundUrl);

            if(isValidUrl(foundUrl)){
                // console.log("enlace valido"); // true
                document.getElementById("edit-company-website-button").setAttribute("onclick",`window.open('${foundUrl}')`);
                document.getElementById("edit-company-website-button").style.display = "flex";
            }else{
                document.getElementById("edit-company-website-button").style.display = "";
            }
            
        }
    });

    // limpiar borde rojo al escribir/cambiar
    $(document).on(
        'input change',
        '#pol_number, #pol_carrier, #pol_url, #pol_expiration, #pol_eff_date, #pol_added_date, #pol_due_day, #pol_status, #pol_agent_record, #edit_pol_number, #edit_pol_carrier, #edit_pol_url, #edit_pol_expiration, #edit_pol_eff_date, #edit_pol_added_date, #edit_pol_due_day, #edit_pol_status, #edit_pol_agent_record',
        function () {
            const value = ($(this).val() || '').trim();
            markFieldError($(this), value === '');
        }
    );

    // =========================================================================
    //   OVERLAY NUEVA POLICY
    // =========================================================================
    $newBtn.on('click', function () {
        $overlayNew.css('display', 'flex').hide().fadeIn(150);
    });

    $cancelNew.on('click', function (e) {
        e.preventDefault();
        $overlayNew.fadeOut(150);
        return false;
    });

    // =========================================================================
    //   GUARDAR NUEVA POLICY
    // =========================================================================
    $saveNew.off('click').on('click', function (e) {
        e.preventDefault();

        if (!validateRequiredFields('')) {
            alert('Please fill in all required policy fields.');
            return false;
        }

        let vehicules = [];

        $('#vehicle-container .vehicle-card').each(function () {
            const $card = $(this);

            const vin = ($card.find('.vin-input').val() || '').trim();

            let year = $card.find('.year-select').val();
            if (!year || year === 'other') {
                year = ($card.find('.year-other').val() || '').trim();
            }

            let make = $card.find('.make-select').val();
            if (!make || make === 'other') {
                make = ($card.find('.make-other').val() || '').trim();
            }

            let model = $card.find('.model-select').val();
            if (!model || model === 'other') {
                model = ($card.find('.model-other').val() || '').trim();
            }

            if (!vin && !year && !make && !model) return;

            vehicules.push({ vin, year, make, model });
        });

        let formData = {
            _token: csrf,
            pol_carrier: $('#pol_carrier').val(),
            pol_number: $('#pol_number').val(),
            pol_url: $('#pol_url').val(),
            pol_expiration: $('#pol_expiration').val(),
            pol_eff_date: $('#pol_eff_date').val(),
            pol_added_date: $('#pol_added_date').val(),
            pol_due_day: $('#pol_due_day').val(),
            pol_status: $('#pol_status').val(),
            pol_agent_record: $('#pol_agent_record').val(),
            vehicules: JSON.stringify(vehicules)
        };

        $.ajax({
            url: storeUrl,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error saving policy');
                }
            },
            error: function (xhr) {
                console.error('Error saving policy:', xhr.responseText);
                alert('Error saving policy');
            }
        });

        return false;
    });

    // =========================================================================
    //   ELIMINAR POLICY
    // =========================================================================
    $('.policy-delete-btn').on('click', function () {
        const url = $(this).data('url');

        if (!confirm('Delete this policy?')) return;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: csrf,
                _method: 'DELETE'
            },
            success: function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert('Error deleting policy');
                }
            },
            error: function (xhr) {
                console.error('Error deleting policy:', xhr.responseText);
                alert('Error deleting policy');
            }
        });
    });

    // =========================================================================
    //   SISTEMA DE VEHÍCULOS (CREACIÓN)
    // =========================================================================
    $('#add-vehicle-btn').on('click', function (e) {
        e.preventDefault();

        const count = $('#vehicle-container .vehicle-card').length;
        if (count >= MAX_VEHICLES) {
            alert('Máximo ' + MAX_VEHICLES + ' vehículos por póliza.');
            return false;
        }

        const id = Date.now();
        $('#vehicle-container').append(createVehicleCardHtml(id));
        initYearsForCard($(`.vehicle-card[data-id='${id}']`));

        return false;
    });

    $(document).on('click', '#vehicle-container .vehicle-delete-btn', function () {
        $(this).closest('.vehicle-card').remove();
    });

    // --- VIN Autocompletar (CREACIÓN) ---
    $(document).on('blur', '.vin-input', function () {
        const vin = $(this).val();
        const $card = $(this).closest('.vehicle-card');

        if (!vin || vin.length < 5) return;

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${vin}?format=json`,
            function (res) {
                if (!res?.Results?.[0]) return;

                const v = res.Results[0];
                const year = v.ModelYear || '';
                const make = v.Make || '';
                const model = v.Model || '';

                const $yearSel = $card.find('.year-select');
                const $makeSel = $card.find('.make-select');
                const $modelSel = $card.find('.model-select');

                if (year) {
                    if ($yearSel.find(`option[value="${year}"]`).length === 0) {
                        $yearSel.append(`<option value="${year}">${year}</option>`);
                    }
                    $yearSel.val(year);
                }

                fillMakeSelect($makeSel, make);

                if (make) {
                    loadModelsForMake($card, make, model, false);
                } else {
                    $modelSel.empty().append('<option value="">Seleccione modelo</option>');
                }

                setTimeout(() => {
                    updateImageForCard($card, make, model, year);
                }, 350);
            }
        );
    });

    // Año -> marcas (CREACIÓN)
    $(document).on('change', '.year-select', function () {
        const $card = $(this).closest('.vehicle-card');
        const year = $(this).val();
        const $makeSel = $card.find('.make-select');
        const $modelSel = $card.find('.model-select');

        $modelSel.empty().append('<option value="">Seleccione modelo</option>');
        $card.find('.year-other').hide().val('');

        if (year === 'other') {
            $(this).hide();
            $card.find('.year-other').show();
            return;
        }

        fillMakeSelect($makeSel);
    });

    // Marca -> modelos (CREACIÓN)
    $(document).on('change', '.make-select', function () {
        const make = $(this).val();
        const $card = $(this).closest('.vehicle-card');

        $card.find('.make-other').hide().val('');
        $card.find('.model-other').hide().val('');
        $card.find('.model-select').show();

        if (make === 'other') {
            $(this).hide();
            $card.find('.make-other').show();
            $card.find('.model-select').empty().append('<option value="">Seleccione modelo</option>');
            return;
        }

        loadModelsForMake($card, make, '', false);
    });

    // Modelo -> imagen (CREACIÓN)
    $(document).on('change', '.model-select', function () {
        const $card = $(this).closest('.vehicle-card');
        const year = $card.find('.year-select').val();
        const make = $card.find('.make-select').val();
        const model = $(this).val();

        $card.find('.model-other').hide().val('');

        if (model === 'other') {
            $(this).hide();
            $card.find('.model-other').show();
            return;
        }

        if (!year || !make || !model) return;
        updateImageForCard($card, make, model, year);
    });

    // =========================================================================
    //   OVERLAY VER / EDITAR POLICY
    // =========================================================================
    $(document).on('click', '.policy-info-btn', function () {
        const showUrl = $(this).data('url');
        const updateUrl = $(this).data('update-url');

        $.get(showUrl, function (res) {
            if (!res || !res.success) {
                alert('Error loading policy data');
                return;
            }

            const p = res.policy;
            let veh = p.vehicules;

            if (typeof veh === 'string') {
                try {
                    veh = JSON.parse(veh);
                } catch (e) {
                    veh = [];
                }
            }
            if (!Array.isArray(veh)) veh = [];

            function isValidUrl(string) {
                try {
                    new URL(string);
                    return true;
                } catch (err) {
                    return false;
                }
            }

            let urlButtonValue;

            if(isValidUrl(p.pol_url)){
                urlButtonValue = `<div id="edit-company-website-button" style="display:flex;" onclick="window.open('${p.pol_url}')">
                    <i class='bx bx-globe'></i>
                </div>`; 

            }else{
                urlButtonValue = `<div id="edit-company-website-button"><i class='bx bx-globe'></i></div>`; 
            }

            let html = `
                <div class="edit-grid">   
                    <div class="edit-left">
                        <label>Policy Number</label>
                        <input type="text" id="edit_pol_number" value="${p.pol_number ?? ''}" required>

                        <label>Carrier</label>
                        <input type="text" id="edit_pol_carrier" value="${p.pol_carrier ?? ''}" required>

                        <label style="position:relative;">URL (Company Website)
                            ${urlButtonValue}
                        </label>
                        <input type="text" id="edit_pol_url" value="${p.pol_url ?? ''}" required>

                        <label>Expiration Date</label>
                        <input type="date" id="edit_pol_expiration" value="${p.pol_expiration ?? ''}" required>

                        <label>Effective Date</label>
                        <input type="date" id="edit_pol_eff_date" value="${p.pol_eff_date ?? ''}" required>

                        <label>Added Date</label>
                        <input type="date" id="edit_pol_added_date" value="${p.pol_added_date ?? ''}" required>

                        <label>Payment Due Day</label>
                        <input type="text" id="edit_pol_due_day" value="${p.pol_due_day ?? ''}" required>

                        <label>Status</label>
                        <input type="text" id="edit_pol_status" value="${p.pol_status ?? ''}" required>

                        <label>Agent Record</label>
                        <input type="text" id="edit_pol_agent_record" value="${p.pol_agent_record ?? ''}" required>
                    </div>

                    <div class="edit-right">
                        <h3>Vehicles</h3>

                        <button type="button" id="add-vehicle-btn-edit" class="btn add-vehicle-btn">
                            <i class='bx bx-car' style="font-size:1.4em"></i>&nbsp; Añadir Vehículo
                        </button>

                        <div class="edit-vehicles-grid"></div>
                    </div>
                </div>
            `;

            $overlayContent.html(html);
            setRequiredPolicyFields('edit_');

            const $grid = $overlayContent.find('.edit-vehicles-grid');

            veh.forEach((v, index) => {
                const realIndex = `${Date.now()}_${index}`;
                $grid.append(createVehicleEditCardHtml(realIndex, v));
                initEditCardValues($(`.vehicle-edit-card[data-index="${realIndex}"]`), v);
            });

            $overlayEdit.fadeIn(150);

            $overlaySave.off('click').on('click', function (e) {
                e.preventDefault();

                if (!validateRequiredFields('edit_')) {
                    alert('Please fill in all required policy fields.');
                    return false;
                }

                let updatedVeh = [];

                $('#policy-edit-content .vehicle-edit-card').each(function () {
                    const $card = $(this);

                    const vin = ($card.find('.edit_vin').val() || '').trim();

                    let year = $card.find('.edit_year_select').val();
                    if (!year || year === 'other') {
                        year = ($card.find('.edit_year_other').val() || '').trim();
                    }

                    let make = $card.find('.edit_make_select').val();
                    if (!make || make === 'other') {
                        make = ($card.find('.edit_make_other').val() || '').trim();
                    }

                    let model = $card.find('.edit_model_select').val();
                    if (!model || model === 'other') {
                        model = ($card.find('.edit_model_other').val() || '').trim();
                    }

                    if (!vin && !year && !make && !model) return;

                    updatedVeh.push({ vin, year, make, model });
                });

                $.ajax({
                    url: updateUrl,
                    type: 'POST',
                    data: {
                        _token: csrf,
                        pol_carrier: $('#edit_pol_carrier').val(),
                        pol_number: $('#edit_pol_number').val(),
                        pol_url: $('#edit_pol_url').val(),
                        pol_expiration: $('#edit_pol_expiration').val(),
                        pol_eff_date: $('#edit_pol_eff_date').val(),
                        pol_added_date: $('#edit_pol_added_date').val(),
                        pol_due_day: $('#edit_pol_due_day').val(),
                        pol_status: $('#edit_pol_status').val(),
                        pol_agent_record: $('#edit_pol_agent_record').val(),
                        vehicules: JSON.stringify(updatedVeh)
                    },
                    success: function (r) {
                        if (r.success) {
                            location.reload();
                        } else {
                            alert('Error updating policy');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error updating policy:', xhr.responseText);
                        alert('Error updating policy');
                    }
                });

                return false;
            });
        });
    });

    // =========================================================================
    //   EVENTOS DE VEHÍCULOS EN EDICIÓN
    // =========================================================================
    $(document).on('click', '#add-vehicle-btn-edit', function (e) {
        e.preventDefault();

        const $grid = $('#policy-edit-content').find('.edit-vehicles-grid');
        if (!$grid.length) return false;

        const count = $grid.find('.vehicle-edit-card').length;
        if (count >= MAX_VEHICLES) {
            alert('Máximo ' + MAX_VEHICLES + ' vehículos permitidos.');
            return false;
        }

        const index = Date.now();
        $grid.append(createVehicleEditCardHtml(index));
        initEditCardValues($(`.vehicle-edit-card[data-index="${index}"]`), {});

        return false;
    });

    $(document).on('click', '#policy-edit-content .vehicle-delete-btn', function () {
        $(this).closest('.vehicle-edit-card').remove();
    });

    // VIN edición
    $(document).on('blur', '.edit_vin', function () {
        const vin = $(this).val();
        const index = $(this).closest('.vehicle-edit-card').data('index');

        if (!vin || vin.length < 5) return;

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${vin}?format=json`,
            function (res) {
                if (!res?.Results?.[0]) return;

                const v = res.Results[0];
                const $card = $(`.vehicle-edit-card[data-index="${index}"]`);

                if (v.ModelYear) {
                    if ($card.find(`.edit_year_select option[value="${v.ModelYear}"]`).length === 0) {
                        $card.find('.edit_year_select').append(`<option value="${v.ModelYear}">${v.ModelYear}</option>`);
                    }
                    $card.find('.edit_year_select').val(v.ModelYear);
                }

                fillMakeSelect($card.find('.edit_make_select'), v.Make || '');

                if (v.Make) {
                    loadModelsForMake($card, v.Make, v.Model || '', true);
                }

                setTimeout(() => {
                    updateEditImage(index);
                }, 350);
            }
        );
    });

    // Year edición
    $(document).on('change', '.edit_year_select', function () {
        const $card = $(this).closest('.vehicle-edit-card');
        const year = $(this).val();

        $card.find('.edit_year_other').hide().val('');

        if (year === 'other') {
            $(this).hide();
            $card.find('.edit_year_other').show();
        }

        updateEditImage($card.data('index'));
    });

    // Make edición
    $(document).on('change', '.edit_make_select', function () {
        const $card = $(this).closest('.vehicle-edit-card');
        const make = $(this).val();

        $card.find('.edit_make_other').hide().val('');
        $card.find('.edit_model_other').hide().val('');
        $card.find('.edit_model_select').show();

        if (make === 'other') {
            $(this).hide();
            $card.find('.edit_make_other').show();
            $card.find('.edit_model_select').empty().append('<option value="">Seleccione modelo</option>');
            return;
        }

        loadModelsForMake($card, make, '', true);
        updateEditImage($card.data('index'));
    });

    // Model edición
    $(document).on('change', '.edit_model_select', function () {
        const $card = $(this).closest('.vehicle-edit-card');
        const model = $(this).val();

        $card.find('.edit_model_other').hide().val('');

        if (model === 'other') {
            $(this).hide();
            $card.find('.edit_model_other').show();
            return;
        }

        updateEditImage($card.data('index'));
    });

    $(document).on('keyup change', '.edit_year_other, .edit_make_other, .edit_model_other', function () {
        const index = $(this).closest('.vehicle-edit-card').data('index');
        updateEditImage(index);
    });

    $(document).on('input change', '#pol_carrier, #edit_pol_carrier', function () {
        const carrierValue = $(this).val();
        const foundUrl = findCarrierUrl(carrierValue);

        if (this.id === 'pol_carrier') {
            $('#pol_url').val(foundUrl);
        } else if (this.id === 'edit_pol_carrier') {
            $('#edit_pol_url').val(foundUrl);
        }

        // console.log("se ejecuta");

    });

    
});

function closeOverlayEdit() {
    const $overlayEdit = $('#policy-edit-overlay');
    $overlayEdit.fadeOut(150);
}