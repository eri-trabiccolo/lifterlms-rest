curl --request POST \
  --url https://example.tld/wp-json/llms/v1/groups/%7Bid%7D \
  --user ck_XXXXXX:cs_XXXXXX \
  --header 'content-type: application/json' \
  --data '{"title":"Getting Started with LifterLMS","content":"<!-- wp:heading -->\\n<h2>Lorem ipsum dolor sit amet.</h2>\\n<!-- /wp:heading -->\\n\\n<!-- wp:paragraph -->\\n<p>Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.</p>\\n<!-- /wp:paragraph -->","excerpt":"Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.","date_created":"2019-05-20 17:22:05","date_created_gmt":"2019-05-20 13:22:05","menu_order":0,"slug":"team-codebox","post":1234,"visibility":"open","logo":1987,"banner":1897}'
