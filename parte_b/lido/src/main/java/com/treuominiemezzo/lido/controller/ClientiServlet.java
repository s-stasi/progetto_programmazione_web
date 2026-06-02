package com.treuominiemezzo.lido.controller;

import org.thymeleaf.TemplateEngine;
import org.thymeleaf.context.WebContext;
import org.thymeleaf.web.IWebExchange;
import org.thymeleaf.web.servlet.JakartaServletWebApplication;

import com.treuominiemezzo.lido.dao.ClienteDAO;
import com.treuominiemezzo.lido.model.Cliente;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.util.List;

// Questa Servlet risponderà all'URL /clienti
@WebServlet("/clienti")
public class ClientiServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        // Impostiamo il content type per il browser
        resp.setContentType("text/html;charset=UTF-8");

        // 1. Inizializziamo Thymeleaf (usando la nostra classe di configurazione)
        TemplateEngine engine = ThymeleafConfig.getTemplateEngine(req.getServletContext());
        
        // 2. Prepariamo il contesto web (l'equivalente di passare le variabili in PHP)
        JakartaServletWebApplication application = JakartaServletWebApplication.buildApplication(req.getServletContext());
        IWebExchange webExchange = application.buildExchange(req, resp);
        WebContext context = new WebContext(webExchange, webExchange.getLocale());

        // 3. Interroghiamo il database tramite il DAO
        ClienteDAO clienteDAO = new ClienteDAO();
        List<Cliente> listaClienti = clienteDAO.getAllClienti();

        System.err.println("DEBUG: Numero di clienti recuperati: " + listaClienti.size());

        // Passiamo la lista al contesto web per Thymeleaf
        context.setVariable("titoloPagina", "Elenco Clienti Lido Sole & Sabbia");
        context.setVariable("clienti", listaClienti); // La chiave "clienti" sarà usata nel th:each

        // 4. Diciamo a Thymeleaf di processare il file "clienti.html" e inviarlo al browser
        // engine.process("clienti", context, resp.getWriter());
        try {
            // 4. Diciamo a Thymeleaf di processare il file "clienti.html"
            engine.process("clienti", context, resp.getWriter());
        } catch (Exception e) {
            // Se Thymeleaf esplode, stampiamo tutto in console!
            System.err.println("!!! ERRORE FATALE DURANTE IL RENDERING DI THYMELEAF !!!");
            e.printStackTrace();
            
            // E mandiamo anche un messaggio chiaro al browser
            resp.getWriter().println("<h2>Errore di sistema: controlla la console di Tomcat!</h2>");
        }
    }
}