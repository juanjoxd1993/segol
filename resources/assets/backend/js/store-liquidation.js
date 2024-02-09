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
		clientsFilter: [],
		prestamos: [],
		articles_for_liquidations: [],
		balones: [],
		warehouse_account_type_id: 0,
		sale_series: [],
		guide_numbers: [],
		scops: [],
		warehouse_type_id: 0,
		articles_filter: [],
		warehouse_type_id_receiver: 0,
		warehouse_document_type_id:0,
	},
	mutations: {
		SET_WAREHOUSE_DOCUMENT_TYPE_ID(state, payload) {
			state.warehouse_document_type_id = payload;
		},
		addModel(state, model) {
			state.model = model;
		},
		addArticles(state, articles) {
			state.articles = articles;
		},
		addSale(state, sale) {
			state.sale = sale;
		},
		addGuideNumber(state, guide_number) {
			state.guide_numbers.push(guide_number);
		},
		addScop(state, scop) {
			state.scops.push(scop);
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
			let article_index = state.articles.findIndex(element => data.article_id == element.article_id);

			if (article) {
				article.new_balance_converted_amount = accounting.toFixed(accounting.unformat(article.new_balance_converted_amount) - accounting.unformat(data.quantity), 4);
				article.sale_converted_amount = accounting.toFixed(accounting.unformat(article.sale_converted_amount) + accounting.unformat(data.quantity), 4);
	
				state.articles[article_index] = article;
			}
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
		addBalones(state, balones) {
			balones.map(item => {
				const balon = state.balones.find(element => element.article_id == item.article_id);
				const balon_index = state.balones.findIndex(element => element.article_id == item.article_id);

				const prestamo = item.prestamo;
				const retorno_press = item.retorno_press;

				if (balon) {
					const rest_prestamo = prestamo - balon.last_prestamo;
					const rest_retorno_press = retorno_press - balon.last_retorno_press;

					const obj = { ...balon }

					obj.retorno_press = parseInt(obj.retorno_press) + parseInt(rest_retorno_press);
					obj.prestamo = parseInt(obj.prestamo) + parseInt(rest_prestamo);

					state.balones[balon_index] = obj;
				} else {
					state.balones.push(item);
				}
			});
		},
		changeStockBalon(state, model) {
			const article_id = model.article_id;
			const amount = model.amount;
			const if_devol = model.if_devol;
			const operation = model.operation;

			const balon_index = state.balones.findIndex(item => item.article_id == article_id);

			if (if_devol) {
				if (operation < 0) {
					state.balones[balon_index].retorno_press -= amount;
				} else {
					state.balones[balon_index].retorno_press = parseInt(state.balones[balon_index].retorno_press) + parseInt(amount);
				}
			} else {
				if (operation < 0) {
					state.balones[balon_index].prestamo -= amount;
				} else {
					state.balones[balon_index].prestamo = parseInt(state.balones[balon_index].prestamo) + parseInt(amount);
				}
			};
		}
	},
	getters: {
		editSale: (state) => (client_id) => {
			return state.sales.find(element => element.client_id == client_id);
		}
	}
}