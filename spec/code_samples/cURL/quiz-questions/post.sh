curl --request POST \
  --url https://example.tld/wp-json/llms/v1/quiz-questions \
  --user ck_XXXXXX:cs_XXXXXX \
  --header 'content-type: application/json' \
  --data '{"date_created":"2019-05-20 17:22:05","date_created_gmt":"2019-05-20 13:22:05","order":1,"parent_id":234,"points":10,"video_embed":"https://www.youtube.com/watch?v=videoid","featured_media":205,"question_type":"choice","choices":[{"marker":"A","choice":"Red","correct":false},{"marker":"B","choice":"Green","correct":true}],"multi_choices":false,"title":"What is your favorite color?","content":"<h2>Lorem ipsum dolor sit amet.</h2>\\n\\n<p>Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.</p>","answer_clarification":"<h2>Lorem ipsum dolor sit amet.</h2>\\n\\n<p>Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.</p>"}'
