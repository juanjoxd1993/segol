export default {
	state: {
		articles: [],
		model: {},
		sales: [],
		sale: {
			client_id: '',
			client_name: '',
			document_type_id: '',
			warehouse_document_type_id: '',
			referral_serie_number: '',
			referral_voucher_number: '',
			referral_guide_series: '',
			referral_guide_number: '',
			details: [],
			liquidations: [],
			total: '',
			perception: '',
			total_perception: '',
			payment_id: '',
			currency_id: 1,
		},
		clients: [],
		articles_for_liquidations: [],
		warehouse_account_type_id: 0,
		sale_series: [],
		warehouse_type_id: 0,
		articles_filter: [],
		warehouse_type_id_receiver: 0
	},
	mutations: {
		addModel(state, model) {
			state.model = model;
		},
		addArticles(state, articles) {
			state.articles = articles;
		},
		addSale(state, sale) {
			state.sale = sale;
		},
		addLiquidations(state, liquidations) {
			state.sale.liquidations = liquidations;
		},
		addSales(state) {
			let sale = JSON.parse(JSON.stringify(state.sale));
			state.sales.push(sale);

			state.sale.client_id = '';
			state.sale.client_name = '';
			state.sale.document_type_id = '';
			state.sale.warehouse_document_type_id = '';
			state.sale.warehouse_document_type_name = '';
			state.sale.referral_serie_number = '';
			state.sale.referral_voucher_number = '';
			state.sale.referral_guide_series = '';
			state.sale.referral_guide_number = '';
			state.sale.details = [];
			state.sale.liquidations = [];
			state.sale.total = '';
			state.sale.perception = '';
			state.sale.total_perception = '';
			state.sale.payment_id = '';
			state.sale.currency_id = 1;

			return 1;
		},
		changeBalanceValue(state, data) {
			let article = state.articles.find(element => data.article_id == element.article_id);
			// article.new_balance_converted_amount = accounting.toFixed(accounting.unformat(article.new_balance_converted_amount) - accounting.unformat(data.quantity), 4);
			// article.sale_converted_amount = accounting.toFixed(accounting.unformat(article.sale_converted_amount) + accounting.unformat(data.quantity), 4);
		},
		resetState(state) {
			state.articles = [];
			state.model = {};
			state.sales = [];
			state.sale = {
				client_id: '',
				client_name: '',
				document_type_id: '',
				warehouse_document_type_id: '',
				warehouse_document_type_name: '',
				referral_serie_number: '',
				referral_voucher_number: '',
				referral_guide_series: '',
				referral_guide_number: '',
				details: [],
				liquidations: [],
				total: '',
				perception: '',
				total_perception: '',
				payment_id: '',
				currency_id: 1,
			}
		},
		addClient(state, client) {
			state.clients.push(client);
		},
		addClients(state, clients) {
			state.clients = clients;
		},
		addArticleForLiquidation(state, article) {
			state.articles_for_liquidations.push(article);
		},
		addArticlesForLiquidations(state, articles) {
			state.articles_for_liquidations = articles;
		},
		registerWarehouseAccountTypeId(state, id) {
			state.warehouse_account_type_id = id;
		},
		setWarehouseTypeId(state, id) {
			state.warehouse_type_id = id;
		},
		setWarehouseTypeIdReceiver(state, id) {
			state.warehouse_type_id_receiver = id;
		},
	},
	getters: {
		editSale: (state) => (client_id) => {
			return state.sales.find(element => element.client_id == client_id);
		}
	}
}