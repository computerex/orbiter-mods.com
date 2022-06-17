// express
const express = require('express');
const app = express();
const port = process.env.PORT || 3001;
// body parser
const bodyParser = require('body-parser');
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// serve static files in public folder
app.use(express.static('public'));

// serve public/index.html
app.get('/', (req, res) => {
    res.sendFile(__dirname + '/public/index.html');
});

// start server
app.listen(port, () => {
    console.log(`Server started on port ${port}`);
}).on('error', (err) => {
    console.log(err);
});